<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/app/verify-account-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/types/requests.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/app/make-connection.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/Recaptcha.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/SendMail.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/make-output.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/use-cors.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/MailTemplateParser.php";

use Classes\Recaptcha;
use Classes\MailTemplateParser;
use Classes\SendMail;

use Types\FormRequests;
use Types\Requests;

use function Lib\useOutputHeader;
use function Lib\makeOutput;
use function Lib\useCorsHeaders;

useOutputHeader();
useCorsHeaders();

$token = $_POST[Requests::recaptchaToken];

//$recaptchaVerify = (new Recaptcha())->verifyScore($token);
//if (!$recaptchaVerify) exit(makeOutput(false, [ "no-recaptcha" ]));


$mailer = new SendMail();
if (!key_exists($_POST[FormRequests::sendTo], $mailer->associations))
    exit(makeOutput(false, [ "no-association" ]));

// Get variables from post request
$sendTo = $mailer->associations[$_POST[FormRequests::sendTo]];
$attachments = $_FILES[FormRequests::attachments];

//$subject = $_POST[FormRequests::subject];
$text = $_POST[FormRequests::text];

$name = $_POST[FormRequests::name];
$address = $_POST[FormRequests::address];

$phone = $_POST[FormRequests::phone];
$email = $_POST[FormRequests::email];

$target = ($_POST[FormRequests::target] ?? "Обращение к");

if (!$sendTo or !$text or !$name or !$address)
    exit(makeOutput(false, [ "no-data" ]));

// Parse mail template
$template = new MailTemplateParser();

$template->addTitle("$target [" . $_POST[FormRequests::sendTo] . "]");
$template->addUserData($name, $address, $phone ?? "Не указан", $email ?? "Не указан");
$template->addMessageText($text);
//$template->addSubject($subject);

// Send mail
$mailer->sendMail([ $sendTo ], [], $template->title, $template->template);
exit(makeOutput(true, [ $attachments ]));
