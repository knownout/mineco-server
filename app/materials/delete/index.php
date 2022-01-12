<?php

/**
 * Endpoint for deleting materials
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/app/verify-account-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/types/requests.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/app/make-connection.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/PathBuilder.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/make-output.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/use-cors.php";

use function Lib\useOutputHeader;
use function Lib\makeOutput;
use function Lib\useCorsHeaders;

use Types\Requests;
use Classes\PathBuilder;

useOutputHeader();
useCorsHeaders();

$accountData = verifyAccountData();
$pathBuilder = new PathBuilder();
$identifier = $_POST[Requests::deleteMaterial];

if (!$accountData) exit(makeOutput(false, [ "auth-error" ]));
if(!isset($identifier)) exit(makeOutput(false, [ "no-identifier" ]));

$database = makeDatabaseConnection();
if(!$database) exit(makeOutput(false, [ "database-error" ]));

$query = $database->query("delete from materials where identifier='$identifier'");
$database->mysqli->close();
$location = $pathBuilder->makePath($pathBuilder->materialsStorage, $identifier . ".json");
if(file_exists($location) and is_file($location))
    unlink($location);

if(!$query) exit(makeOutput(false, [ "query-error" ]));
exit(makeOutput(true, $identifier));