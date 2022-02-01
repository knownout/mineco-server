<?php

/**
 * Endpoint for obtaining full material content.
 * This endpoint will be used not only by control panel, so
 * I called it "get" instead of "preview"
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/types/requests.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/Recaptcha.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/use-cors.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/PathBuilder.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/make-output.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/app/make-connection.php";

use Classes\PathBuilder;
use Classes\Recaptcha;
use Types\Requests;
use function Lib\makeOutput;
use function Lib\useCorsHeaders;
use function Lib\useOutputHeader;

$pathBuilder = new PathBuilder();

// Get recaptcha token and target file name
//$token = $_POST[Requests::recaptchaToken];
$identifier = $_POST[Requests::getMaterial];

// Attach CORS headers
useCorsHeaders();
useOutputHeader();

if (!isset($identifier)) exit(makeOutput(false, [ "no-data-provided" ]));

// Verify request with Google reCAPTCHA token
//$recaptchaVerify = (new Recaptcha())->verifyScore($token);
//if (!$recaptchaVerify) exit(http_response_code(404));

$database = makeDatabaseConnection();
if (!$database) exit(makeOutput(false, [ "no-database-connection" ]));

$query = $database->query("select name from tags order by identifier");
if(!$query) exit(makeOutput(false, [ "query-fail" ]));

$tagsList = $query->fetch_all();

$parsedTagsList = [];
foreach ($tagsList as $item) $parsedTagsList[] = $item[0];

if ($identifier === "create-new") {
    exit(makeOutput(true, [ "data" => [
        "identifier" => "", "title" => "",
        "tags" => "", "description" => "",
        "preview" => "_default-minecoBuilding.jpg",
        "datetime" => "" . time(), "pinned" => "0",
        "attachments" => ""
    ], "content" => "", "tags" => $parsedTagsList ]));
}

$contentFilePath = $pathBuilder->makePath($pathBuilder->materialsStorage, $identifier . ".json");

// Check if both database entry and content file exist
// If not, there is some cleaning mechanism
if (!file_exists($contentFilePath)) {
    $database->query("delete from materials where identifier='$identifier'");
    exit(makeOutput(false, [ "no-material-file" ]));
}

$materialData = $database->query("select * from materials where identifier='$identifier'")->fetch_assoc();
$database->mysqli->close();

$contentFile = @json_decode(@file_get_contents($contentFilePath));
if (!$materialData or !$contentFile or !isset($contentFile)) {
    unlink($contentFilePath);
    exit(makeOutput(false, [ "no-database-entry-or-file" ]));
}

exit(makeOutput(true, [ "data" => $materialData, "content" => $contentFile, "tags" => $parsedTagsList ]));