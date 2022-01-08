<?php

/**
 * Endpoint for updating variables in the database
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/app/make-connection.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/types/requests.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/make-output.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/use-cors.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/app/verify-account-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/PathBuilder.php";

use Classes\PathBuilder;
use Types\VariableRequests;
use function Lib\useOutputHeader;
use function Lib\makeOutput;
use function Lib\useCorsHeaders;

useCorsHeaders();
useOutputHeader();

$pathBuilder = new PathBuilder();

$accountData = verifyAccountData();
if (!$accountData) exit(makeOutput(false, [ "auth-failed" ]));

$variable = $_POST[ VariableRequests::name ];
$value = $_POST[ VariableRequests::update ];

$database = makeDatabaseConnection();
if (!$database) exit(makeOutput(false, [ "no-database-connection" ]));

$query = $database->query("update variables set value='$value' where name='$variable'");
if (!$query) exit(makeOutput(false, [ "query-error" ]));

exit(makeOutput(true, $value));