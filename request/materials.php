<?php

require_once "../workers/MaterialsRequest.php";
require_once "../workers/StandardLibrary.php";
require_once "../types/RequestActionsList.php";
require_once "../types/RequestTypesList.php";
require_once "../types/MaterialsSearchOptions.php";

use Workers\MaterialsRequest;
use Workers\StandardLibrary;
use Types\RequestActionsList;
use Types\RequestTypesList;

/**
 * Get data from $_POST request
 * @param string $request RequestTypesList constant
 * @return mixed|null value or null
 */
$getPostRequest = fn(string $request) => isset($_POST[$request]) ? $_POST[$request] : null;

$request = $getPostRequest(RequestTypesList::Action);
$account = [
    "login" => $getPostRequest(RequestTypesList::AccountLogin),
    "hash" => $getPostRequest(RequestTypesList::AccountHash)
];

// If no request specified, return error
if (is_null($request)) StandardLibrary::returnJsonOutput(false, "request not specified");

$materialsRequest = new MaterialsRequest();

switch ($request)
{
    // Request all tags list from database
    case RequestActionsList::getTagsList:
        StandardLibrary::returnJsonOutput(true, $materialsRequest->getTagsList());
        break;

    // Request one latest pinned material from db descending sorted by time
    case RequestActionsList::getPinnedMaterial:
        $pinned = $materialsRequest->requestPinnedMaterial();
        if (is_null($pinned)) StandardLibrary::returnJsonOutput(false, "no pinned materials");

        StandardLibrary::returnJsonOutput(true, $pinned);
        break;

    // Get latest materials from db (without pinned) of specific tag descending sorted by time
    case RequestActionsList::getLatestMaterials:
        $tag = $getPostRequest(RequestTypesList::DataTag);
        if(is_null($tag)) $tag = "Новости";

        $materials = $materialsRequest->requestLatestMaterials($tag);
        if(is_null($materials)) StandardLibrary::returnJsonOutput(false, "no materials found");

        StandardLibrary::returnJsonOutput(true, $materials);
        break;

    // Return error if undefined request name
    default:
        StandardLibrary::returnJsonOutput(false, "unknown request");
}