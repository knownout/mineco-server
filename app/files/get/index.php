<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/types/requests.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/Recaptcha.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/PathBuilder.php";

use Classes\PathBuilder;
use Classes\Recaptcha;
use Types\Requests;

$token = $_POST[ Requests::recaptchaToken ];
$filename = $_GET["file"];
$download = $_GET["download"];

//$recaptchaVerify = (new Recaptcha())->verifyScore($token);
//if (!$recaptchaVerify) exit(http_response_code(403));
if (!$filename) exit(http_response_code(404));

$pathBuilder = new PathBuilder();
$location = $pathBuilder->makePath($pathBuilder->fileStorage, $filename);
if (!file_exists($location) or !is_file($location)) exit(http_response_code(404));

header("Content-Type: " . mime_content_type($location));
header("Content-Length: " . filesize($location));

if (isset($download) and $download === "true") {
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . basename($location) . "\"");
} else if(!in_array(pathinfo($location)["extension"], [ "txt", "jpg", "jpeg", "png", "pdf" ]))
    exit(http_response_code(404));

readfile($location);