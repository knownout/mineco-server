<?php

require_once "../types/RequestTypesList.php";
require_once "../controllers/AccountsController.php";
require_once "../controllers/StandardLibrary.php";
require_once "../controllers/LogController.php";
require_once "../controllers/FileController.php";
require_once "../server-config.php";

use Controllers\AccountsController;
use Controllers\FileController;
use Controllers\LogController;
use Controllers\MaterialRequestController;
use Controllers\StandardLibrary;
use Types\RequestTypesList;


class ModificationHandler extends AccountsController
{
    private LogController $logger;
    private MaterialRequestController $controller;

    public function __construct ()
    {
        parent::__construct();
        $this->logger = new LogController();
        $this->controller = new MaterialRequestController();
    }

    /**
     * Change account password in database (auth required)
     */
    public function changePassword ()
    {
        // Get data from $_POST request
        $newHash = MetadataHandler::requestData(RequestTypesList::AccountNewHash);
        [ $verification, $login, $hash ] = parent::verifyWithPostData();

        // Check if authentication data provided
        if (!$verification)
            StandardLibrary::returnJsonOutput(false, "auth data invalid");

        // Check if new password provided and if it is an md5 hash
        if (is_null($newHash) or strlen($newHash) != 32)
            StandardLibrary::returnJsonOutput(false, "new password hash not specified or invalid");


        // Send request to database (this method compares auth data with database)
        $result = parent::changeAccountPassword($login, $hash, $newHash);

        if (!$result) StandardLibrary::returnJsonOutput(false, "password not changed");

        $this->logger->saveAction(LogController::PasswordChange, $login, $login);
        StandardLibrary::returnJsonOutput(true, "password changed");
    }

    /**
     * Read and compare user account data
     */
    public function verifyAccountData ()
    {
        $verification = parent::verifyWithPostData();
        $returnData = $verification[0]
            ? [ "name" => isset($verification[3]) ? $verification[3] : null ]
            : "invalid auth data";

        StandardLibrary::returnJsonOutput($verification[0], $returnData);
    }

    /**
     * Update material db entry (for time, title, identifier and tags) and
     * files on drive (content file, preview, images, files)
     */
    public function updateMaterial ()
    {
        // Verify authentication data
        [ $verification, $login ] = parent::verifyWithPostData();
        if (!$verification) StandardLibrary::returnJsonOutput(false, "auth data invalid");

        // Get identifier of the affected material
        $identifier = MetadataHandler::requestData(RequestTypesList::DataIdentifier);
        if (is_null($identifier)) StandardLibrary::returnJsonOutput(false, "identifier not specified");

        // Shortcut for transforming RequestTypesList constant to inline tag
        $transform = fn(string $s) => strtolower(explode(":", $s)[1]);

        // Result for each affected tag (or null if not affected)
        $affectResult = [ $transform(RequestTypesList::UpdateContent) => null ];

        // List of tags that can be affected
        $affect = [
            RequestTypesList::UpdateIdentifier,
            RequestTypesList::UpdatePinned,
            RequestTypesList::UpdateTitle,
            RequestTypesList::UpdateTime,
            RequestTypesList::UpdateTags,
            RequestTypesList::UpdateShort
        ];

        // Add data to tags list and placeholders for results list
        foreach ($affect as $item)
        {
            $affect[$item] = MetadataHandler::requestData($item);
            $affectResult[$transform($item)] = null;
        }

        // Remove data with numeric keys
        $affect = array_slice($affect, count($affect) / 2, count($affect));

        // Get material metadata
        $material = $this->controller->requestMaterialByIdentifier($identifier);
        if (is_null($material)) StandardLibrary::returnJsonOutput(false, "material not found");

        // Function to compare specified tags data with current material data
        $compare = function (string $key) use ($material, $affect, $transform)
        {
            // Function used for compare array-like values (Value, Value, ...)
            $trim = fn($s) => is_null($s) ? $s : preg_replace("/\s*([,.])\s+/", "$1", $s);

            // If affected title, compare without trim function
            if ($key == RequestTypesList::UpdateTitle) return $material[$transform($key)] === $affect[$key];

            // Check if pinned state true or not if affected
            if ($key == RequestTypesList::UpdatePinned)
                return $material[$transform($key)] === ($affect[$key] == "true" ? "1" : "0");

            return $trim($material[$transform($key)]) === $trim($affect[$key]);
        };

        /** Rewrite database content */
        foreach ($affect as $key => $item)
        {
            // Compare key and check if key null or length lower than one
            if ($compare($key) || is_null($item) || strlen($item) < 1) continue;
            $value = $item;

            // If key is pinned state, set value one or zero depending on item value
            if ($key == RequestTypesList::UpdatePinned) $value = $item == "true" ? "1" : "0";

            // ... if key is time, set value only if time is integer and greater than zero
            else if ($key == RequestTypesList::UpdateTime) $value = ((int)$item > 0 and strlen($item) == 13)
                ? $item
                : null;

            // ... otherwise set value as mysql string
            else $value = "'{$value}'";

            // if value not set (invalid), skip
            if (is_null($value))
            {
                $affectResult[$transform($key)] = "invalid value";
                continue;
            }

            $result = $this->connection->query(
                "UPDATE materials SET {$transform($key)}={$value} WHERE identifier='{$identifier}'"
            );

            $affectResult[$transform($key)] = $result == false ? "database error" : true;
            $material[$transform($key)] = $affect[$key];
        }

        /** Rewrite content file */
        global $MaterialsPath;
        $materialsPath = $MaterialsPath . $identifier . ".json";

        // Get new content
        $content = MetadataHandler::requestData(RequestTypesList::UpdateContent);
        if (!is_null($content))
        {
            $key = $transform(RequestTypesList::UpdateContent);

            // Validate json content
            $validation = FileController::validateJson($content);

            if ($validation !== true) $affectResult[$key] = $validation;
            else $affectResult[$key] = (file_put_contents($materialsPath, $content) ? true : "file write error");
        }

        // Shortcut for new identifier if exist
        $newIdentifier = $affect[RequestTypesList::UpdateIdentifier];

        // Write action to database
        $this->logger->saveAction(
            LogController::MaterialUpdate, $login,
            is_null($newIdentifier) ? $identifier : "{$identifier} => {$newIdentifier}"
        );

        StandardLibrary::returnJsonOutput(true, [ "material" => $material, "affect" => $affectResult ]);
    }

    /**
     * Fully remove material from drive and database
     *
     * Warning! Method recursively remove data from material root folder
     */
    public function removeMaterial ()
    {
        $identifier = MetadataHandler::requestData(RequestTypesList::DataIdentifier);
        [ $verification, $login ] = parent::verifyWithPostData();

        if (!$verification) StandardLibrary::returnJsonOutput(false, "auth data invalid");

        global $MaterialsPath;
        $materialPath = $MaterialsPath . $identifier . DIRECTORY_SEPARATOR;

        $this->connection->query("DELETE FROM materials WHERE identifier='{$identifier}'");

        $removeDirectory = FileController::removeDirectory($materialPath);
        if ($removeDirectory == FileController::DirectoryNotExist)
            StandardLibrary::returnJsonOutput(false, "material not found");

        if ($removeDirectory == FileController::Successful)
        {
            $this->logger->saveAction(LogController::MaterialRemove, $login, $identifier);
            StandardLibrary::returnJsonOutput(true, "material removed");
        } else StandardLibrary::returnJsonOutput(false, "material not removed");
    }

    /**
     * Update specific property value in database
     */
    public function updateProperty ()
    {
        [ $verification ] = parent::verifyWithPostData();

        if(!$verification) StandardLibrary::returnJsonOutput(false, "auth data invalid");

        $property = MetadataHandler::requestData(RequestTypesList::Property);
        $value = MetadataHandler::requestData(RequestTypesList::PropertyValue);

        $this->connection->query("UPDATE properties SET value='{$value}' WHERE property='{$property}'");
        if ($this->connection->error) StandardLibrary::returnJsonOutput(false, $this->connection->error);
        else StandardLibrary::returnJsonOutput(true, null);
    }
}
