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
        $captcha = MetadataHandler::verifyCaptchaRequest();
        if (!$captcha) StandardLibrary::returnJsonOutput(false, "recaptcha verification failed");

        return $modificationHandler->changePassword();

    // Verify provided account data
    case RequestActionsList::verifyAccount:
        // Check if recaptcha token provided and score greater than or equal to 0.3
        $captcha = MetadataHandler::verifyCaptchaRequest();
        if (!$captcha) StandardLibrary::returnJsonOutput(false, "recaptcha verification failed");

        return $modificationHandler->verifyAccountData();

    /** FILES UPLOAD SECTION */
    // Upload specific file to file storage (except images)
    case RequestActionsList::uploadFile:
        return $filesHandler->uploadFile();

    // Get list of all files in file storage directory (without images)
    case RequestActionsList::getFilesList:
        return $filesHandler->getFilesList(false);

    // These two are the same as file requests, but for images

    case RequestActionsList::getImagesList:
        return $filesHandler->getFilesList(true);

    case RequestActionsList::getFullMaterial:
        return $metadataHandler->getFullMaterial();

    /** Google recaptcha token verification */

    case RequestActionsList::verifyCaptchaRequest:
        return MetadataHandler::verifyCaptchaRequest();

    /** Properties get/update requests */
    case RequestActionsList::getFromProperties:
        return $metadataHandler->getFromProperties();

    case RequestActionsList::updateProperty:
        $captcha = MetadataHandler::verifyCaptchaRequest();
        if (!$captcha) StandardLibrary::returnJsonOutput(false, "recaptcha verification failed");

        return $modificationHandler->updateProperty();

    /** UNKNOWN REQUESTS HANDLER */
    // Return error if undefined request name
    default:
        StandardLibrary::returnJsonOutput(false, "unknown request");
}
