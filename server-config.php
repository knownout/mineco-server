<?php

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

global $MaterialsPath, $UserContentPath, $ImageSize, $ImageQuality;
$MaterialsPath = join(DIRECTORY_SEPARATOR, [ $_SERVER["DOCUMENT_ROOT"], "user-content", "" ]);

// If you want to change user files storage location, simple change "user-storage" to your folder name
// Add entries to array to add path segments: ["a", "b", "c"] => a/b/c/
$UserContentPath = join(
    DIRECTORY_SEPARATOR, [ $_SERVER["DOCUMENT_ROOT"], "user-storage", "" ]
);

// All uploaded images (except preview) will be scaled to this size
$ImageSize = [ 1280, 720 ];

// All uploaded images (preview too) will has this quality
$ImageQuality = 89; // Maximal value is 100