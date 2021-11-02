<?php

require_once "RequestHeadersHandler.php";
require_once "RequestUpdateHandler.php";
require_once "../types/RequestActionsList.php";

use Types\RequestActionsList;
use Types\RequestTypesList;
use Workers\StandardLibrary;

$request = RequestHeadersHandler::getPostRequest(RequestTypesList::Action);
$account = [
    "login" => RequestHeadersHandler::getPostRequest(RequestTypesList::AccountLogin),
    "hash" => RequestHeadersHandler::getPostRequest(RequestTypesList::AccountHash)
];

// If no request specified, return error
if (is_null($request)) StandardLibrary::returnJsonOutput(false, "request not specified");
$headerHandler = new RequestHeadersHandler();
$updateHandler = new RequestUpdateHandler();

switch ($request)
{
    /** READ-ONLY SECTION */
    // Request all tags list from database
    case RequestActionsList::getTagsList:
        return $headerHandler->getTagsList();

    // Request one latest pinned material from db descending sorted by time
    case RequestActionsList::getPinnedMaterial:
        return $headerHandler->getLatestPinnedMaterial();

    //Get latest materials from db (without pinned) of specific tag descending sorted by time
    case RequestActionsList::getLatestMaterials:
        return $headerHandler->getMaterials();

    /** READ-WRITE SECTION */
    // Update material on server with client data
    case RequestActionsList::updateMaterial:
        return $updateHandler->updateMaterial();

    case RequestActionsList::removeMaterial:
        return $updateHandler->removeMaterial();

    /** ACCOUNTS READ-WRITE SECTION */
    // Change account password
    case RequestActionsList::changePassword:
        return $updateHandler->changePassword();

    /** UNKNOWN REQUESTS HANDLER */
    // Return error if undefined request name
    default:
        StandardLibrary::returnJsonOutput(false, "unknown request");
}