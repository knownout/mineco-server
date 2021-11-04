<?php

require_once "../controllers/AccountsController.php";
require_once "../controllers/LogController.php";
require_once "../controllers/StandardLibrary.php";

use Controllers\AccountsController;
use Controllers\LogController;
use Controllers\StandardLibrary;

class FilesHandler extends AccountsController
{
    private LogController $logger;

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
            // List of files (all, files and images) in current directory
            $files = array_filter(array_values(
                array_diff(scandir($path), [ "..", "." ])), fn($i) => is_file($path . $i)
            );

            foreach ($files as $file)
            {
                $info = pathinfo($path . $file);

                // If images needed, skip files and vice versa
                if (!$images and $info["extension"] == "jpg") continue;
                else if ($images and $info["extension"] != "jpg") continue;

                // Write to data tree
                $fileSystem[$directory][$file] = [
                    "extension" => $info["extension"],
                    "name" => $info["filename"],
                    "size" => stat($path . $file)["size"]
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
        global $UserContentPath;
        $path = $UserContentPath . date("m-Y") . DIRECTORY_SEPARATOR;

        $fileName = time() . "@" . $file["name"];
        move_uploaded_file($file["tmp_name"], $path . $fileName);
        StandardLibrary::returnJsonOutput(true, [ "name" => $fileName, "date" => date("m-Y") ]);
    }

    /**
     * Upload file to specific directory on a server (images upload not allowed)
     */
    public function uploadFile ()
    {
        // I dont know why i using md5 key here, but it looks cool and safe (actually no...)
        $file = $_FILES["24DE53B2C0A9E15844AE9B37E9B52EC8"];

        if (isset($file))
        {
            if ($file["type"] == "image/jpeg")
                StandardLibrary::returnJsonOutput(false, "uploading images here not allowed");

            $this->moveUploadedFile($file);
        }

        StandardLibrary::returnJsonOutput(false, "no file specified");
    }

    /**
     * Upload image to specific folder (images only)
     */
    public function uploadImage ()
    {
        // Same as the previous one...
        $file = $_FILES["A12CFE4AB396E25FD2431247A5961A9A"];

        if (isset($file))
        {
            if ($file["type"] != "image/jpeg")
                StandardLibrary::returnJsonOutput(false, "uploading non-jpeg files here not allowed");

            $this->moveUploadedFile($file);
        }

        StandardLibrary::returnJsonOutput(false, "no image specified");
    }
}