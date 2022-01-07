<?php

/**
 * Endpoint for searching materials in the database
 *
 * Returns common json output with filename with
 * database entries as associative arrays
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/QueryBuilder.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/Search.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/use-cors.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/make-output.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/types/requests.php";

use Classes\QueryBuilder;
use Classes\Search;

use function Lib\useCorsHeaders;
use function Lib\makeOutput;
use function Lib\useOutputHeader;

use Types\CommonSearchRequests;
use Types\MaterialSearchRequests;

useCorsHeaders();
useOutputHeader();

$queryBuilder = new QueryBuilder("materials");

// Parse all possible POST requests
$constants = (new ReflectionClass(new MaterialSearchRequests))->getConstants();
foreach ($constants as $key => $constant) {
    // Specific logic for tags request (parse like array)
    if ($key === "tags") {
        $postValue = $_POST[MaterialSearchRequests::tags];
        if(!isset($postValue)) continue;

        $subQuery = [];
        $tagQueryArray = explode(",", $postValue);
        foreach ($tagQueryArray as $item) $subQuery[] = "tags like '%{$item}%'";

        $queryBuilder->addQuery(join(" and ", $subQuery));

    } else if($key === "content") { // Specific login for the content request (search in description and title)
        $postValue = $_POST[MaterialSearchRequests::content];
        if(!isset($postValue)) continue;

        $queryBuilder->addQuery("title like '%$postValue%' or description like '%$postValue%'");
    }
    else $queryBuilder->addFromPost($constant[0], $constant[1]);
}

$queryBuilder->orderBy("datetime")->setLimitFromPost(CommonSearchRequests::limit);

// Execute search with Search class
$response = (new Search($queryBuilder))->execute();

if(!$response) exit(makeOutput(false, [ "no-response" ]));
exit(makeOutput(true, $response));