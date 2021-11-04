<?php

require_once "MetadataHandler.php";
require_once "ModificationHandler.php";
require_once "FilesHandler.php";

require_once "../types/RequestActionsList.php";

use Controllers\StandardLibrary;
use Types\RequestActionsList;
use Types\RequestTypesList;

$request = MetadataHandler::requestData(RequestTypesList::Action);
$account = [
    "login" => MetadataHandler::requestData(RequestTypesList::AccountLogin),
    "hash" => MetadataHandler::requestData(RequestTypesList::AccountHash)
];

// If no request specified, return error
if (is_null($request)) StandardLibrary::returnJsonOutput(false, "request not specified");
$headerHandler = new MetadataHandler();
$modificationHandler = new ModificationHandler();
$filesHandler = new FilesHandler();

switch ($request)
{
    /** READ-ONLY SECTION */
    // Request all tags list from database
    case RequestActionsList::getTagsList:
        return $headerHandler->getTags();

    // Request one latest pinned material from db descending sorted by time
    case RequestActionsList::getPinnedMaterial:
        return $headerHandler->getLatestPinnedMaterial();

    //Get latest materials from db (without pinned) of specific tag descending sorted by time
    case RequestActionsList::getMaterials:
        return $headerHandler->getMaterials();

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
    case RequestActionsList::uploadImage:
        return $filesHandler->uploadFile();

    case RequestActionsList::uploadFile:
        return $filesHandler->uploadImage();

    case RequestActionsList::getFilesList:
        return $filesHandler->getFilesList(false);

    case RequestActionsList::getImagesList:
        return $filesHandler->getFilesList(true);

    /** UNKNOWN REQUESTS HANDLER */
    // Return error if undefined request name
    default:
        StandardLibrary::returnJsonOutput(false, "unknown request");
}