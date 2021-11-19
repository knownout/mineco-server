<?php

require_once "../controllers/StandardLibrary.php";
require_once "../controllers/FileController.php";
require_once "../request/MetadataHandler.php";
require_once "../server-config.php";

use Controllers\FileController;
use Controllers\StandardLibrary;

StandardLibrary::setCorsHeaders();

$extensionIcon = MetadataHandler::requestData("extension_icon", $_GET);
$image = MetadataHandler::requestData("image", $_GET);
$stub = MetadataHandler::requestData("stub", $_GET);

$date = MetadataHandler::requestData("date", $_GET);
$file = MetadataHandler::requestData("file", $_GET);

if (isset($extensionIcon)) // Get icon for file extension, if exist
{
    global $ExtensionIconsPath;
    $extensionIcon = mb_strtolower($extensionIcon);

    $iconFileName = null;
    foreach (array_diff(scandir($ExtensionIconsPath), [ ".", "..", "unknown.png" ]) as $icon)
    {
        $filename = pathinfo($icon)["filename"];
        $regex = "/(" . str_replace(",", "|", $filename) . ").{0,1}$/";

        $match = preg_match($regex, $extensionIcon);

        if ($match)
        {
            $iconFileName = $icon;
            break;
        }
    }

    if (!$iconFileName) $iconFileName = "unknown.png";

    if (!is_file($ExtensionIconsPath . $iconFileName))
        http_response_code(404);
    else
    {
        header("Content-type: image/png");
        readfile($ExtensionIconsPath . $iconFileName);
    }

} else if (isset($image))
{
    global $ImagesPath;
    $path = $ImagesPath . $image;

    if (!file_exists($path) or explode("/", mime_content_type($path))[0] != "image")
        http_response_code(404);
    else
    {
        header("Content-type: " . mime_content_type($path));
        readfile($path);
    }
} else if (isset($date) and isset($file)) // Check if required data specified by user
{
    global $UserContentPath, $TempFolder;
    $path = $UserContentPath . $date . DIRECTORY_SEPARATOR . $file;

    // If file not exist, return 404
    if (!is_file($path)) http_response_code(404);
    else
    {
        $mime = mime_content_type($path);

        // Check mime content and download or open file to view (for images only)
        if ($mime == "image/jpeg")
        {
            // Open image
            header("Content-type: {$mime}");

            if (isset($stub) and $stub == "true")
            {
                [ $width, $height ] = getimagesize($path);

                $stubFilePath = $TempFolder . time() . "@" . $file;
                FileController::resizeImage($path, $stubFilePath, 50, 50, 30);
                FileController::resizeImage($stubFilePath, $stubFilePath, $width, $height, 20, true);

                readfile($stubFilePath);

                unlink($stubFilePath);
            } else readfile($path);
        } else
        {
            // Download file
            header("Content-Disposition: attachment; filename=" . $file);

            // Downloading on Android might fail without this
            header("Content-type: {$mime}");
            ob_clean();

            readfile($path);
        }
    }
    // If required data not specified, return 404
} else http_response_code(404);
