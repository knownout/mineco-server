<?php

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

$identifier = $_POST[Requests::updateMaterial];
$content = $_POST[Requests::updateMaterialContent];
$text = $_POST[Requests::updateMaterialText];

if (!$accountData) exit(makeOutput(false, [ "auth-error" ]));
if (!isset($identifier) or !isset($content)) exit(makeOutput(false, [ "no-identifier" ]));

$content = @json_decode($content, true);
$text = @json_decode($text, true);
if (!$content or !$text) exit(makeOutput(false, [ "invalid-content" ]));

$database = makeDatabaseConnection();
if (!$database) exit(makeOutput(false, [ "no-database-connection" ]));

$queryList = [];
$keysList = [];
$valuesList = [];

foreach ($content as $key => $value) {
    $queryList[] = "$key='$value'";
    $keysList[] = $key;
    $valuesList[] = "'$value'";
}

if ($content["pinned"] === "1") $database->query("update materials set pinned=0");

$queryString = "insert into materials (" . join(", ", $keysList) . ") values ("
    . join(", ", $valuesList) . ") on duplicate key update"
    . " " . join(", ", $queryList);

$query = $database->query($queryString);
$database->mysqli->close();

if (!$query) exit(makeOutput(false, [ "query-error", $queryString ]));

$textFilePath = $pathBuilder->makePath($pathBuilder->materialsStorage, $identifier . ".json");
file_put_contents($textFilePath, json_encode($text,
    JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));


if ($identifier !== $content["identifier"]) rename(
    $textFilePath,
    $pathBuilder->makePath($pathBuilder->materialsStorage, $content["identifier"] . ".json")
);

exit(makeOutput(true, [ $content["identifier"] ]));
