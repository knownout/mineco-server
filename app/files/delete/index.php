<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/types/requests.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/app/make-connection.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/app/verify-account-data.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/make-output.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/use-cors.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/PathBuilder.php";

use Classes\PathBuilder;
use function Lib\useOutputHeader;
use function Lib\makeOutput;
use function Lib\useCorsHeaders;

use Types\Requests;

useCorsHeaders();
useOutputHeader();

$pathBuilder = new PathBuilder();
$accountData = verifyAccountData();
if (!$accountData) exit(makeOutput(false, [ "auth-failed" ]));

$filename = $_POST[Requests::deleteFile];
if(!isset($filename)) exit(makeOutput(false, [ "no-file" ]));

$database = makeDatabaseConnection();
if(!$database) exit(makeOutput(false, [ "database-error" ]));

$query = $database->query("delete from files where filename='$filename'");
if(!$query) exit(makeOutput(false, [ "query-error" ]));

$location = $pathBuilder->makePath($pathBuilder->fileStorage, $filename);
if(file_exists($location) and is_file($location))
    unlink($location);

exit(makeOutput(true, $filename));