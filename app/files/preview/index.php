<?php

/**
 * Endpoint for requiring file preview
 * (for images and text files only)
 *
 * Returns 404 http response code if error or not found, or
 * file content with specific Content-Type header
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/PathBuilder.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/types/requests.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/Recaptcha.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/use-cors.php";

use Classes\Recaptcha;
use Types\Requests;
use function Lib\useCorsHeaders;

$container = new PathBuilder();

// Get recaptcha token and target file name
$token = $_POST[ Requests::recaptchaToken ];
$filename = $_POST[ Requests::getFilePreview ];

// Attach CORS headers
useCorsHeaders();

// Verify request with Google reCAPTCHA token
$recaptchaVerify = (new Recaptcha())->verifyScore($token);
if (!$recaptchaVerify) exit(http_response_code(404));

// Check if filename provided
if(!isset($filename)) exit(http_response_code(404));

// Check if file extension is allowed
$ext = pathinfo($filename)["extension"];
if(!in_array($ext, [ "txt", "png", "jpeg", "jpg" ])) exit(http_response_code(404));

// Check if file exist
$location = $container->makePath($container->fileStorage, $filename);
if(!file_exists($location)) exit(http_response_code(404));

header("Content-Length: " . filesize($location));
header("Content-Type: " . mime_content_type($location));

readfile($location);