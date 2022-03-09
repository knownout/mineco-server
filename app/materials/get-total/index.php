<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/make-output.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/app/make-connection.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/use-cors.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/types/requests.php";

use Types\MaterialSearchRequests;
use function Lib\makeOutput;
use function Lib\useCorsHeaders;
use function Lib\useOutputHeader;

// Attach CORS headers
useCorsHeaders();
useOutputHeader();

$tag = $_POST[MaterialSearchRequests::tags];

$database = makeDatabaseConnection();
if (!$database) exit(makeOutput(false, [ "no-database-connection" ]));

$stringQuery = "select count(*) from materials where datetime < " . time();
if (isset($tag)) $stringQuery .= " and tags like '%$tag%'";

$query = $database->query($stringQuery);
if (!$query) exit(makeOutput(false, [ "query-fail" ]));

$materialsCount = $query->fetch_row();
exit(makeOutput(true, intval($materialsCount[0])));