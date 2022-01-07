<?php

/**
 * Endpoint for uploading files to the server
 *
 * Returns common json output with filename
 * as response if upload successful
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/make-output.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/use-cors.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/types/requests.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/app/make-connection.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/app/verify-account-data.php";

use function Lib\useOutputHeader;
use function Lib\makeOutput;
use function Lib\useCorsHeaders;

use Types\Requests;

useCorsHeaders();
useOutputHeader();

$accountData = verifyAccountData();
if (!$accountData) exit(makeOutput(false, [ "auth-failed" ]));

$file = $_FILES[ Requests::uploadFile ];
if (!isset($file) or !isset($file["name"])) exit(makeOutput(false, [ "no-file" ]));

$directory = $_SERVER["DOCUMENT_ROOT"] . "\\storage\\files-storage\\";
$filename = $file["name"];

if(file_exists($directory . $filename)) exit(makeOutput(false, [ "file-exist" ]));
if (move_uploaded_file($file["tmp_name"], $directory . $filename)) {
    $database = makeDatabaseConnection();

    $time = time();
    $extension = $file["extension"];

    $result = $database->query(
        "INSERT INTO files (filename,datetime,extension) VALUES ('$filename',$time,'$extension')"
    );

    if($result) exit(makeOutput(true, $filename));
    else exit(makeOutput(false, [ "database-insert-error" ]));
}
else exit(makeOutput(false, [ "upload-error", $file, $directory ]));