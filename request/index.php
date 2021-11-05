<?php

require_once "MetadataHandler.php";
require_once "ModificationHandler.php";
require_once "FilesHandler.php";

require_once "../types/RequestActionsList.php";

use Controllers\StandardLibrary;
use Types\RequestActionsList;
use Types\RequestTypesList;

StandardLibrary::setCorsHeaders();

if (isset($_SERVER["CONTENT_LENGTH"]))
{
    if ($_SERVER["CONTENT_LENGTH"] > ((int)ini_get('post_max_size') * 1024 * 1024))
        exit(http_response_code(413));
}

$request = MetadataHandler::requestData(RequestTypesList::Action);
$account = [
    "login" => MetadataHandler::requestData(RequestTypesList::AccountLogin),
    "hash" => MetadataHandler::requestData(RequestTypesList::AccountHash)
];

// If no request specified, return error
if (is_null($request)) StandardLibrary::returnJsonOutput(false, "request not specified");
$metadataHandler = new MetadataHandler();
$modificationHandler = new ModificationHandler();
$filesHandler = new FilesHandler();

switch ($request)
{
    /** READ-ONLY SECTION */
    // Request all tags list from database
    case RequestActionsList::getTagsList:
        return $metadataHandler->getTags();

    // Request one latest pinned material from db descending sorted by time
    case RequestActionsList::getPinnedMaterial:
        return $metadataHandler->getLatestPinnedMaterial();

    //Get latest materials from db (without pinned) of specific tag descending sorted by time
    case RequestActionsList::getMaterials:
        return $metadataHandler->getMaterials();

    /** READ-WRITE SECTION */
    // Update material on server with client data
    case RequestActionsList::updateMaterial:
        return $modificationHandler->updateMaterial();

    case RequestActionsList::removeMaterial:
        return $modificationHandler->removeMaterial();

    /** ACCOUNTS READ-WRITE SECTION */
    // Change account password
    case RequestActionsList::changePassword:
        return $modificationHandler->changePassword();

    /** FILES UPLOAD SECTION */
    case RequestActionsList::uploadFile:
        return $filesHandler->uploadFile();

    case RequestActionsList::getFilesList:
        return $filesHandler->getFilesList(false);

    case RequestActionsList::getImagesList:
        return $filesHandler->getFilesList(true);

    case RequestActionsList::getFullMaterial:
        return $metadataHandler->getFullMaterial();

    /** UNKNOWN REQUESTS HANDLER */
    // Return error if undefined request name
    default:
        StandardLibrary::returnJsonOutput(false, "unknown request");
}