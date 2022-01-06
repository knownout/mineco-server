<?php

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

use Types\FilesSearchPostRequests;
use Types\CommonSearchPostRequests;

useCorsHeaders();
useOutputHeader();

$queryBuilder = new QueryBuilder("files");
$queryBuilder->addFromPost(...FilesSearchPostRequests::fileName);

$queryBuilder->orderBy("identifier");
$queryBuilder->setLimitFromPost(CommonSearchPostRequests::limit);

$response = (new Search($queryBuilder))->requireResponse(false);

if(!$response) exit(makeOutput(false, [ "no-response" ]));
exit(makeOutput(true, $response));