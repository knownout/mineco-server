<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/make-output.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/app/make-connection.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/use-cors.php";

use function Lib\makeOutput;
use function Lib\useCorsHeaders;
use function Lib\useOutputHeader;

// Attach CORS headers
useCorsHeaders();
useOutputHeader();

$database = makeDatabaseConnection();
if (!$database) exit(makeOutput(false, [ "no-database-connection" ]));

$query = $database->query("select count(*) from materials where datetime < " . time());
if (!$query) exit(makeOutput(false, [ "query-fail" ]));

$materialsCount = $query->fetch_row();
exit(makeOutput(true, intval($materialsCount[0])));