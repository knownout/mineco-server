<?php

require_once "../controllers/AccountsController.php";
require_once "../controllers/LogController.php";
require_once "../controllers/StandardLibrary.php";
require_once "../controllers/FileController.php";

use Controllers\AccountsController;
use Controllers\FileController;
use Controllers\LogController;
use Controllers\StandardLibrary;

class FilesHandler extends AccountsController
{
    private LogController $logger;
    public array $fileUpload_errorMessages = [
        "ok",
        "file exceeds php max file size",
        "file exceeds html max file size",
        "partially uploaded",
        "no file",
        "no temporary directory",
        "disk write fail",
        "php extension error"
    ];

    public function __construct ()
    {
        parent::__construct();

        $this->logger = new LogController();
    }

    /**
     * Get list of files (without images) or images (without files)
     * @param bool $images if true return list of images
     */
    public function getFilesList (bool $images = false)
    {
        [ $verification ] = parent::verifyWithPostData();
        if (!$verification) StandardLibrary::returnJsonOutput(false, "auth data invalid");

        global $UserContentPath;
        $path = $UserContentPath . date("m-Y") . DIRECTORY_SEPARATOR;

        if (!is_dir($path)) mkdir($path);

        // Get list of special-named directories
        $directories = array_filter(array_values(array_diff(
            scandir($UserContentPath), [ "..", "." ])),
            fn($i) => (is_dir($UserContentPath . $i) and strlen($i) == 7)
        );

        // Container of whole data tree
        $fileSystem = [];
        foreach ($directories as $directory)
        {
            $local = $UserContentPath . $directory . DIRECTORY_SEPARATOR;
            // List of files (all, files and images) in current directory
            $files = array_filter(array_values(
                array_diff(scandir($local), [ "..", "." ])), fn($i) => is_file($local . $i)
            );

            foreach ($files as $file)
            {
                $info = pathinfo($local . $file);

                // If images needed, skip files and vice versa
                if ($images and mime_content_type($local . $file) != "image/jpeg") continue;

                // Write to data tree
                $fileSystem[$directory][$file] = [
                    "extension" => $info["extension"],
                    "name" => $info["filename"],
                    "size" => stat($local . $file)["size"]
                ];
            }
        }

        StandardLibrary::returnJsonOutput(true, $fileSystem);
    }

    /**
     * Move uploaded file (technical)
     * @param $file
     */
    private function moveUploadedFile ($file)
    {
        [ $verification ] = parent::verifyWithPostData();
        if (!$verification) StandardLibrary::returnJsonOutput(false, "auth data invalid");

        global $UserContentPath;
        $path = $UserContentPath . date("m-Y") . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) mkdir($path);

        $fileName = time() . "@" . $file["name"];
        if (isset($file["error"]) and $file["error"] > 0)
            StandardLibrary::returnJsonOutput(false, $this->fileUpload_errorMessages[$file["error"]]);

        move_uploaded_file($file["tmp_name"], $path . $fileName);

        global $ImageSize, $ImageQuality;
        if (mime_content_type($path . $fileName) == "image/jpeg")
            FileController::resizeImage(
                $path . $fileName, $path . $fileName, $ImageSize[0], $ImageSize[1], $ImageQuality
            );

        StandardLibrary::returnJsonOutput(true, [ "name" => $fileName, "date" => date("m-Y") ]);
    }

    /**
     * Upload file to specific directory on a server (images upload not allowed)
     */
    public function uploadFile ()
    {
        // I dont know why i using md5 key here, but it looks cool and safe (actually no...)
        if (isset($_FILES["24DE53B2C0A9E15844AE9B37E9B52EC8"]))
            $this->moveUploadedFile($_FILES["24DE53B2C0A9E15844AE9B37E9B52EC8"]);

        StandardLibrary::returnJsonOutput(false, "no file specified");
    }
}
