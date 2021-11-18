<?php

require_once "controllers/FileController.php";

use Controllers\FileController;

/**
 * Warning! Modification of this variables can lead to unhandled exceptions!
 */

const DatabaseOptions = [
    "hostname" => "127.0.0.1",
    "username" => "root",
    "password" => "",
    "database" => "mineco",
    "port" => "3306"
];

global $MaterialsPath, $UserContentPath, $ImageSize, $ImageQuality, $AllowAllOrigins, $CaptchaSecretKey,
       $ExtensionIconsPath, $ImagesPath;

$MaterialsPath = join(DIRECTORY_SEPARATOR, [ $_SERVER["DOCUMENT_ROOT"], "user-content", "" ]);

// If you want to change user files storage location, simple change "user-storage" to your folder name
// Add entries to array to add path segments: ["a", "b", "c", ""] => a/b/c/
$UserContentPath = join(
    DIRECTORY_SEPARATOR, [ $_SERVER["DOCUMENT_ROOT"], "user-storage", "" ]
);

// All uploaded images (except preview) will be scaled to this size (ratio-friendly)
$ImageSize = [ 1280, 720 ];

// All uploaded images (preview too) will has this quality
$ImageQuality = 95; // Maximal value is 100

// If true, CORS header Access-Control-Allow-Origin will allow all origins (*)
$AllowAllOrigins = true;

// Path to file extensions icons folder
$ExtensionIconsPath = join(DIRECTORY_SEPARATOR, [ $_SERVER["DOCUMENT_ROOT"], "public", "file-extensions", "" ]);

// Path to images directory
$ImagesPath = join(DIRECTORY_SEPARATOR, [ $_SERVER["DOCUMENT_ROOT"], "public", "images", "" ]);

if (!is_dir($MaterialsPath)) mkdir($MaterialsPath);
if (!is_dir($UserContentPath)) mkdir($UserContentPath);

// Secret keys stored in file, ignored by git
$CaptchaSecretKey = FileController::decodeJsonString(
    @file_get_contents($_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "secret-keys.json")
)["recaptcha"];
