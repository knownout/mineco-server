<?php

require_once "../types/RequestTypesList.php";
require_once "../controllers/AccountsController.php";
require_once "../controllers/StandardLibrary.php";
require_once "../controllers/LogController.php";
require_once "../server-config.php";

use Controllers\MaterialRequestController;
use Types\MaterialSearchOptions;
use Types\RequestTypesList;
use Controllers\AccountsController;
use Controllers\StandardLibrary;
use Controllers\LogController;


class ModificationHandler extends AccountsController
{
    private LogController $logger;
    private MaterialRequestController $controller;

    private function post ($request)
    {
        return MetadataHandler::requestData($request);
    }

    private function updateState ($identifier, $condition)
    {
        $this->connection->query(
            "UPDATE materials SET {$condition} WHERE identifier='{$identifier}'"
        );

        return $this->controller->connection->error
            ? $this->controller->connection->error
            : true;
    }

    /**
     * Verify is account data is valid
     * @return array verification result
     */
    private function verifyAuthentication ()
    {
        $login = $this->post(RequestTypesList::AccountLogin);
        $hash = $this->post(RequestTypesList::AccountHash);

        if (is_null($login) or is_null($hash)) return [false, $login, $hash];
        return [parent::compareAccountData($login, $hash), $login, $hash];
    }

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
        $newHash = $this->post(RequestTypesList::AccountNewHash);
        [$verification, $login, $hash] = $this->verifyAuthentication();

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
     * Update material db entry (for time, title, identifier and tags) and
     * files on drive (content file, preview, images, files)
     */
    public function updateMaterial () // FIXME only database modification implemented, no server files affect yet
    {
        // Shortcut for mysqli string escape function
        $escape = fn(string $str) => $this->connection->real_escape_string($str);

        // Identifier of the affected material
        $identifier = $this->post(RequestTypesList::DataIdentifier);

        // If specified, identifier of the material will be changed
        $newIdentifier = $this->post(RequestTypesList::UpdateIdentifier);

        // Modified data of content.json file (just rewrite server file)
        // FIXME not implemented
        $content = $this->post(RequestTypesList::UpdateContent);

        // Metadata values receiving
        $pinned = $this->post(RequestTypesList::UpdatePinned);
        $title = $this->post(RequestTypesList::UpdateTitle);
        $time = (int)$this->post(RequestTypesList::UpdateTime);
        $tags = $this->post(RequestTypesList::UpdateTags);

        // Auth process
        [$verification, $login] = $this->verifyAuthentication();
        if (!$verification) StandardLibrary::returnJsonOutput(false, "auth data invalid");

        // Require material metadata
        $material = $this->controller->requestMaterialByIdentifier($identifier);

        // Check if material metadata exist and received
        if (is_null($material)) StandardLibrary::returnJsonOutput(false, "material not found");

        // Shortcut for checking if a value has changed
        $allow = fn($var, string $key) => !is_null($var) and $material[$key] != $var;

        // Update state (null - not changed, true - changed, string - error)
        $updateState = [
            "identifier" => null,
            "content" => null,
            "pinned" => null,
            "title" => null,
            "time" => null,
            "tags" => null
        ];

        // If identifier changed
        if ($allow($newIdentifier, "identifier"))
        {
            // Patterns for new identifier converting
            $patterns = [
                "/\_{1,}/" => "_",
                "/\-{1,}/" => " ",
                "/\s{1,}/" => "-",
                "/[^А-Яа-яёЁA-Za-z0-9_\-]/" => "",
                "/-_-/" => "_",
            ];

            $newIdentifier = preg_replace(array_keys($patterns), array_values($patterns), $newIdentifier);
            $newIdentifier = mb_strtolower($newIdentifier);

            // Update database and local state
            $updateState["identifier"] =
                $this->updateState($identifier, "identifier='{$escape($newIdentifier)}'");
        }

        // If title changed (mostly like identifier change)
        if ($allow($title, "title"))
        {
            // Patterns for title (mostly special symbols)
            $patterns = [
                "/\s{2,}/" => " ",
                "/_{2,}/" => "_",
                "/\-\-{1,}/" => "–",
                "/\<\</" => "«",
                "/\>\>/" => "»"
            ];

            $title = preg_replace(array_key_first($patterns), array_values($patterns), $newIdentifier);

            // Update database and local state (same as identifier update)
            $updateState["title"] =
                $this->updateState($identifier, "title='{$escape($title)}'");
        }

        // Update tags (custom expression for detect tags even if they written without space after comma)
        if ($allow(preg_replace(["/,/", "/\s{2,}/"], [", ", " "], $tags), "tags"))
        {
            // Explode and trim tags list (to remove extra spaces) and then join with ", "
            $tags = join(", ", array_map(fn($i) => $escape(trim($i)), explode(",", $tags)));

            // Update db and local state
            $updateState["tags"] = $this->updateState($identifier, "tags='{$tags}'");
        }

        // If material pinned state changed
        if ($allow(($pinned == "true" ? 1 : 0), "pinned"))
        {
            // Determine new material state
            $pinState = $pinned == "true" ? 1 : 0;

            // Unpin all others pinned materials if this material pinned
            // (only one material can be pinned at a time)
            if ($pinState == 1) $this->connection->query("UPDATE materials SET pinned=0 WHERE pinned=1");

            // Update db and local state
            $updateState["pinned"] = $this->updateState($identifier, "pinned={$pinState}");
        }

        // If material time changed
        if ($allow($time, "time") and $time > 0)
            $updateState["time"] = $this->updateState($identifier, "time='{$time}'");

        // Parse files list for this material
        // FIXME not implemented
        $filesList = [];
        foreach ($_FILES as $file)
            array_push($filesList, $file);

        // Get material metadata
        // FIXME maybe repeated parsing not necessary?
        $options = new MaterialSearchOptions();
        $options->identifier = is_null($newIdentifier) ? $identifier : $newIdentifier;

        $this->logger->saveAction(
            LogController::MaterialUpdate,
            $login,
            is_null($newIdentifier) ? $identifier : "{$identifier} => {$newIdentifier}"
        );

        StandardLibrary::returnJsonOutput(true, [
            "material" => $this->controller->getMaterialsMeta($options, 1),
            "affect" => $updateState
        ]);
    }

    /**
     * Fully remove material from drive and database
     *
     * Warning! Method recursively remove data from material root folder
     */
    public function removeMaterial ()
    {
        $identifier = $this->post(RequestTypesList::DataIdentifier);
        [$verification, $login] = $this->verifyAuthentication();

        if (!$verification) StandardLibrary::returnJsonOutput(false, "auth data invalid");

        global $MaterialsPath;
        $materialPath = $MaterialsPath . $identifier . DIRECTORY_SEPARATOR;

        $this->connection->query("DELETE FROM materials WHERE identifier='{$identifier}'");
        if (!is_dir($materialPath)) StandardLibrary::returnJsonOutput(false, "material not found");

        try
        {
            $it = new RecursiveDirectoryIterator($materialPath, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it,
                RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($files as $file)
            {
                if ($file->isDir()) rmdir($file->getRealPath());
                else unlink($file->getRealPath());
            }

            $this->logger->saveAction(LogController::MaterialRemove, $login, $identifier);
        } catch (Exception $e)
        {
            StandardLibrary::returnJsonOutput(false, "recursive files remove error");
        }

        $removeDirectory = rmdir($materialPath);
        StandardLibrary::returnJsonOutput(
            $removeDirectory, $removeDirectory ?
            "material removed" :
            "material root folder not removed"
        );
    }
}