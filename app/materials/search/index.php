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

use Types\CommonSearchPostRequests;
use Types\MaterialSearchPostRequests;

useCorsHeaders();
useOutputHeader();

$queryBuilder = new QueryBuilder("materials");

$constants = (new ReflectionClass(new MaterialSearchPostRequests))->getConstants();
foreach ($constants as $key => $constant) {
    if ($key === "tags") {
        $postValue = $_POST[MaterialSearchPostRequests::tags];
        if(!isset($postValue)) continue;

        $subQuery = [];
        $tagQueryArray = explode(",", $postValue);
        foreach ($tagQueryArray as $item) $subQuery[] = "tags like '%{$item}%'";

        $queryBuilder->addQuery(join(" and ", $subQuery));

    } else if($key === "content") {
        $postValue = $_POST[MaterialSearchPostRequests::content];
        if(!isset($postValue)) continue;

        $queryBuilder->addQuery("title like '%$postValue%' or description like '%$postValue%'");
    }
    else $queryBuilder->addFromPost($constant[0], $constant[1]);
}

$queryBuilder->orderBy("datetime");
$queryBuilder->setLimitFromPost(CommonSearchPostRequests::limit);

$response = (new Search($queryBuilder))->requireResponse(false);

if(!$response) exit(makeOutput(false, [ "no-response" ]));
exit(makeOutput(true, $response));