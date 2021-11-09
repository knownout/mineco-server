<?php

require_once "../controllers/MaterialRequestController.php";
require_once "../controllers/StandardLibrary.php";
require_once "../controllers/FileController.php";
require_once "../types/RequestTypesList.php";
require_once "../types/MaterialSearchOptions.php";
require_once "../server-config.php";

use Controllers\FileController;
use Controllers\MaterialRequestController;
use Controllers\StandardLibrary;
use Types\MaterialSearchOptions;
use Types\RequestTypesList;

class MetadataHandler extends MaterialRequestController
{
    public function __construct ()
    {
        parent::__construct();
    }

    /**
     * Return all tags list as request output
     */
    public function getTags ()
    {
        StandardLibrary::returnJsonOutput(true, parent::getTagsList());
    }

    /**
     * Return one latest pinned material as request output
     */
    public function getLatestPinnedMaterial ()
    {
        $pinned = parent::requestPinnedMaterial();
        if (is_null($pinned)) StandardLibrary::returnJsonOutput(false, "no pinned materials");

        StandardLibrary::returnJsonOutput(true, $pinned);
    }

    /**
     * Return latest materials as request output
     *
     * Request options for FormData:
     *
     * Data:Tag - specify tag for materials search, if not specified, used default news tag
     *
     * Data:FindPinned - if true (string) may search for both pinned and unpinned materials, default is false
     *
     * Data:Limit - specify count (string -> int) of materials, default is 10
     */
    public function getMaterials ()
    {
        // Shortcuts for necessary functions
        $post = fn(string $req) => MetadataHandler::requestData($req);
        $escape = fn(string $str) => $this->connection->real_escape_string($str);

        // Retrieving data from $_POST requests
        $tag = $post(RequestTypesList::DataTag);
        $findPinned = $post(RequestTypesList::DataFindPinned);
        $limit = $post(RequestTypesList::DataLimit);

        $title = $post(RequestTypesList::DataTitle);
        $timeStart = $post(RequestTypesList::DataTimeStart);
        $timeEnd = $post(RequestTypesList::DataTimeEnd);
        $offset = $post(RequestTypesList::DataOffset);

        $identifier = $post(RequestTypesList::DataIdentifier);

        // Check if limit is specified and safely convert limit value to integer
        $limit = is_null($limit) ? null : (int)$escape($limit);

        // Create search parameters container
        $options = new MaterialSearchOptions();
        $options->pinned = is_null($findPinned) ? false : ($findPinned == "true" ? null : false);

        // Add request parameters to search parameters object with escaping strings
        if (!is_null($identifier)) $options->identifier = $escape($identifier);
        else
        {
            if (!is_null($title)) $options->title = $escape($title);
            if (!is_null($timeStart)) $options->time_start = (int)$escape($timeStart);
            if (!is_null($timeEnd)) $options->time_end = (int)$escape($timeEnd);
            if (!is_null($tag)) $options->tag = $escape($tag);
            if (!is_null($offset)) $options->offset = (int)$escape($offset);
        }

        $materials = parent::requestMaterials($options, $limit);
        if (is_null($materials)) StandardLibrary::returnJsonOutput(false, "no materials found");

        StandardLibrary::returnJsonOutput(true, $materials);
    }

    /**
     * Get material json data from file and meta content
     */
    public function getFullMaterial ()
    {
        // Identifier of the material
        $identifier = MetadataHandler::requestData(RequestTypesList::DataIdentifier);
        if (is_null($identifier)) StandardLibrary::returnJsonOutput(false, "identifier not specified");

        global $MaterialsPath;
        $path = $MaterialsPath . $identifier . ".json";

        if (!is_file($path)) StandardLibrary::returnJsonOutput(false, "material not found");

        $result = @file_get_contents($path);
        if (!$result) StandardLibrary::returnJsonOutput(false, "material reading error");

        // Simple json validation (can be parsed or not)
        $validation = FileController::validateJson($result);

        if ($validation !== true) StandardLibrary::returnJsonOutput(false, $validation);
        else
        {
            $material = $this->requestMaterialByIdentifier($identifier, [ "title", "tags", "time" ]);
            if (is_null($material)) StandardLibrary::returnJsonOutput(false, "no material database entry");

            StandardLibrary::returnJsonOutput(true, [
                "content" => FileController::decodeJsonString($result),
                "data" => $material
            ]);
        }
    }

    /**
     * Get data from $_POST request
     * @param string $request RequestTypesList constant
     * @return mixed|null value or null
     */
    public static function requestData (string $request)
    {
        return isset($_POST[$request]) ? $_POST[$request] : null;
    }
}
