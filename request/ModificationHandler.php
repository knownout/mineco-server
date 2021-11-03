<?php

require_once "../types/RequestTypesList.php";
require_once "../controllers/AccountsController.php";
require_once "../controllers/StandardLibrary.php";
require_once "../controllers/LogController.php";
require_once "../controllers/FileController.php";
require_once "../server-config.php";

use Controllers\FileController;
use Controllers\MaterialRequestController;
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
    public function updateMaterial () // FIXME file and image loading testing needed, especially image scaling
    {
        // Shortcut for mysqli string escape function
        $escape = fn(string $str) => $this->connection->real_escape_string($str);

        // Identifier of the affected material
        $identifier = $this->post(RequestTypesList::DataIdentifier);

        // If specified, identifier of the material will be changed
        $newIdentifier = $this->post(RequestTypesList::UpdateIdentifier);

        // Modified data of content.json file (just rewrite server file)
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

            $material["identifier"] = $newIdentifier;
            $identifier = $newIdentifier;
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

            $material["title"] = $title;
        }

        // Update tags (custom expression for detect tags even if they written without space after comma)
        if (strlen(trim($tags)) > 0 and $allow(preg_replace(["/,/", "/\s{2,}/"], [", ", " "], $tags), "tags"))
        {
            // Explode and trim tags list (to remove extra spaces) and then join with ", "
            $tags = join(", ", array_map(fn($i) => $escape(trim($i)), explode(",", $tags)));

            // Update db and local state
            $updateState["tags"] = $this->updateState($identifier, "tags='{$tags}'");
            $material["tags"] = $tags;
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
            $material["pinned"] = $pinState;
        }

        // If material time changed
        if ($allow($time, "time") and $time > 0)
        {
            $updateState["time"] = $this->updateState($identifier, "time={$time}");
            $material["time"] = $time;
        }

        // Parse files list for this material
        if (!is_null($content))
        {
            global $MaterialsPath;
            $materialPath = $MaterialsPath . $material["identifier"] . DIRECTORY_SEPARATOR;

            $removeDirectory = FileController::removeDirectory($materialPath, false);

            // If exception while removing content folder from disk, skip files modification
            if ($removeDirectory == FileController::RemovingException)
                $updateState["content"] = "Root directory remove exception";
            else
            {
                global $ImageQuality, $ImageSize;

                // Read file content as json object (decode)
                $contentData = FileController::decodeJsonString($content);

                // Check if file contains blocks section
                if (!property_exists($contentData, "blocks"))
                    $updateState["content"] = "Invalid content file";
                else
                {
                    foreach ($contentData["blocks"] as $block)
                    {
                        mkdir($materialPath);
                        $type = $block["type"];

                        // If type is not image or attaches, skip block
                        if (!in_array(["image", "attaches"], $type)) continue;

                        // Get file identifier from url property (set-up at front-end code)
                        $fileIdentifier = $contentData["blocks"][$block]["data"]["file"]["url"];

                        // Get file object from $_FILES
                        $file = $_FILES[$fileIdentifier];

                        // If file not set, remove this block and skip
                        if (!isset($file))
                        {
                            unset($contentData["block"][$block]);
                            continue;
                        }

                        // Make relative path to files container for current type
                        $filePath = $materialPath . $type == "image" ? "images" : "files"
                            . DIRECTORY_SEPARATOR . $file["name"];

                        // Try to move file to destination, if exception, remove block and skip
                        if (!move_uploaded_file($file["tmp_name"], $filePath))
                        {
                            unset($contentData["block"][$block]);
                            continue;
                        }

                        // If file type is image, resize it to (max) HD size
                        if ($type == "image") FileController::resizeImage(
                            $filePath, $filePath, $ImageSize[0], $ImageSize[1], $ImageQuality
                        );

                        // Make link to server-side handlers for download or get files/images
                        $handler = $type == "image" ? "image" : "download";

                        // Replace block url property
                        $contentData["block"][$block]["data"]["file"]["url"] = "/request/attachments/"
                            . "{$handler}.php?material={$identifier}&attachment={$file["name"]}";
                    }

                    // If preview specified, upload all types of preview
                    if (isset($_FILES["preview"]))
                    {
                        // Move original file
                        move_uploaded_file(
                            $_FILES["preview"]["tmp_name"],
                            $materialPath . "preview" . DIRECTORY_SEPARATOR . "preview-original.jpg"
                        );

                        $preview = $materialPath . "preview" . DIRECTORY_SEPARATOR . "preview-original.jpg";
                        FileController::removeImageExifData($preview, $ImageQuality);

                        $previewDirectory = $materialPath . "preview" . DIRECTORY_SEPARATOR;
                        $path = fn($i) => "{$previewDirectory}preview{$i}.jpg";

                        // Create preview for open graph
                        FileController::resizeImage(
                            $preview, $path("-og"), 480, 270, $ImageQuality
                        );

                        // Create preview for title page
                        FileController::resizeImage(
                            $preview, $path("-large"), 300, 400, $ImageQuality
                        );

                        // Create preview for materials list
                        FileController::resizeImage(
                            $preview, $path("-tile"), 256, 256, $ImageQuality
                        );
                    }

                    // Encode affected object to string and rewrite content file
                    $contentString = FileController::encodeJsonString($contentData);
                    file_put_contents($materialPath . "content.json", $contentString);

                    $updateState["content"] = true;
                }
            }
        }

        // Save action to database
        $this->logger->saveAction(
            LogController::MaterialUpdate,
            $login,
            is_null($newIdentifier) ? $identifier : "{$identifier} => {$newIdentifier}"
        );

        StandardLibrary::returnJsonOutput(true, [
            "material" => $material,
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

        $removeDirectory = FileController::removeDirectory($materialPath);
        if ($removeDirectory == FileController::DirectoryNotExist)
            StandardLibrary::returnJsonOutput(false, "material not found");

        if ($removeDirectory == FileController::Successful)
        {
            $this->logger->saveAction(LogController::MaterialRemove, $login, $identifier);
            StandardLibrary::returnJsonOutput(true, "material removed");
        } else StandardLibrary::returnJsonOutput(false, "material not removed");
    }
}