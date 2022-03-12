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

$file = $_FILES[Requests::uploadFile];
if (!isset($file) or !isset($file["name"])) exit(makeOutput(false, [ "no-file" ]));

$directory = $_SERVER["DOCUMENT_ROOT"] . "\\storage\\files-storage\\";
$filename = date("m-Y") . "\/"
    . pathinfo($file["name"])["filename"] . "." . strtolower(pathinfo($file["name"])["extension"]);

$filename = date("m-Y") . DIRECTORY_SEPARATOR
    . str_replace("&", "", str_replace(" ", "-", pathinfo($file["name"])["filename"])) . "."
    . strtolower(pathinfo($file["name"])["extension"]);

$location = $pathBuilder->makePath($pathBuilder->fileStorage, str_replace(" ", "-", $filename));

if (file_exists($location)) exit(makeOutput(false, [ "file-exist", $location ]));

$directoryPath = $pathBuilder->makePath($pathBuilder->fileStorage, date("m-Y"));
if (!file_exists($directoryPath)) mkdir($directoryPath);

if (move_uploaded_file($file["tmp_name"], $location)) {
    $database = makeDatabaseConnection();

    $time = time();

    $filenameArray = explode(".", $filename);
    $extension = end($filenameArray);

    if (in_array($extension, [ "jpg", "png", "jpeg" ])) {
        [ $width ] = getimagesize($location);

        if($width > 1280) {
            if (in_array($extension, [ "jpg", "jpeg" ])) $image = imagecreatefromjpeg($location);
            else $image = imagecreatefrompng($location);

            $scaledImage = imagescale($image, 1280);
            if (in_array($extension, [ "jpg", "jpeg" ])) imagejpeg($scaledImage, $location, 85);
            else imagepng($scaledImage, $location);
        }
    }


    $result = $database->query(
        "INSERT INTO files (filename,datetime,extension) VALUES ('$filename','$time','$extension')"
    );

    $database->mysqli->close();
    if ($result) exit(makeOutput(true, $filename));
    else {
        unlink($location);
        exit(makeOutput(false, [ "database-insert-error" ]));
    }
} else exit(makeOutput(false, [ "upload-error", $file, $directory ]));