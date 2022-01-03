<?php

require_once "../../types/requests.php";
require_once "../../lib/make-output.php";
require_once "../../lib/use-cors.php";

require_once "../../classes/Recaptcha.php";
require_once "../../classes/Database.php";

use Types\PostRequests;
use Classes\Database;
use Classes\Recaptcha;

use function Lib\useOutputHeader;
use function Lib\makeOutput;
use function Lib\useCorsHeaders;

$login = $_POST[ PostRequests::accountLogin ];
$hash = $_POST[ PostRequests::accountHash ];
$token = $_POST[ PostRequests::recaptchaToken ];

useCorsHeaders();
useOutputHeader();
if (is_null($login) or is_null($hash) or is_null($token))
    exit(makeOutput(false, [ "no-auth-data" ]));

$recaptchaVerify = (new Recaptcha())->verifyScore($token);
if(!$recaptchaVerify) exit(makeOutput(false, [ "recaptcha-verify-false" ]));

$database = new Database();

$connectionResult = $database->connectToDatabase();
if (!$connectionResult) exit(makeOutput(false, [ "no-database-connection" ]));

$query = $database->query("SELECT login,hash,fullname FROM accounts WHERE active=1 AND login='$login'");
if (!$query) exit(makeOutput(false, [ "request-exception" ]));

$accountData = $query->fetch_assoc();
if ($query->num_rows !== 1 or is_null($accountData) or !$accountData)
    exit(makeOutput(false, [ "invalid-account" ]));

if ($accountData["hash"] !== $hash) exit(makeOutput(false, [ "invalid-account-password" ]));
exit(makeOutput(true, $accountData));
