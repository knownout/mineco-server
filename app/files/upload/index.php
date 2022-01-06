<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/make-output.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/use-cors.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/types/requests.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/app/make-connection.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/app/authenticate.php";

use function Lib\useOutputHeader;
use function Lib\makeOutput;
use function Lib\useCorsHeaders;

use Types\PostRequests;

useCorsHeaders();
useOutputHeader();

$accountData = authenticate();
if (!$accountData) exit(makeOutput(false, [ "auth-failed" ]));

$file = $_FILES[ PostRequests::uploadFile ];
if (!isset($file) or !isset($file["name"])) exit(makeOutput(false, [ "no-file" ]));

$directory = $_SERVER["DOCUMENT_ROOT"] . "\\storage\\files-storage\\";
$filename = $file["name"];

if(file_exists($directory . $filename)) exit(makeOutput(false, [ "file-exist" ]));
if (move_uploaded_file($file["tmp_name"], $directory . $filename)) {
    $database = makeDatabaseConnection();
    $result = $database->query("INSERT INTO files (filename) VALUES ('$filename')");

    if($result) exit(makeOutput(true, $filename));
    else exit(makeOutput(false, [ "database-insert-error" ]));
}
else exit(makeOutput(false, [ "upload-error", $file, $directory ]));