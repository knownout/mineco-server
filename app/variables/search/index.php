<?php

/**
 * Endpoint for searching variables in the
 * database
 *
 * Returns common json output with database
 * entries as associative arrays
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

use Types\FileSearchRequests;
use Types\CommonSearchRequests;

useCorsHeaders();
useOutputHeader();

// Build query with filename form POST request
$queryBuilder = new QueryBuilder("variables");
$queryBuilder->addFromPost(...FileSearchRequests::fileName);

$queryBuilder->orderBy("identifier")->setLimitFromPost(CommonSearchRequests::limit);

// Execute search process
$response = (new Search($queryBuilder))->execute();

if(!$response) exit(makeOutput(false, [ "no-response" ]));
exit(makeOutput(true, $response));