<?php

require_once "../controllers/StandardLibrary.php";

use Controllers\StandardLibrary;

StandardLibrary::setCorsHeaders();
$date = MetadataHandler::requestData("date");
$file = MetadataHandler::requestData("file");

// Check if required data specified by user
if (isset($date) and isset($file))
{
    global $UserContentPath;
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
            readfile($path);
        } else
        {
            // Download file
            header('Content-Disposition: attachment; filename=' . $file);

            // Downloading on Android might fail without this
            header('Content-Type: application/octet-stream');
            ob_clean();

            readfile($path);
        }
    }
    // If required data not specified, return 404
} else http_response_code(404);
