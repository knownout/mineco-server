<?php

require_once "../../lib/cors.php";
require_once "../../lib/recaptcha.php";
require_once "../../lib/makeJsonOutput.php";
require_once "../../lib/database.php";

require_once "../../types/requests.php";

use Lib\Cors;
use Lib\Recaptcha;
use Lib\MakeJsonOutput;
use Lib\Database;

use Types\Requests;

Cors::useCors();

$token = $_POST[Requests::recaptchaToken];
$login = $_POST[Requests::accountLogin];
$hash = $_POST[Requests::accountHash];

if(!$login or !$hash)
    exit(MakeJsonOutput::errorOutput([ "no-auth-data" ]));

$recaptcha = (new Recaptcha($token))->verify();

if ($recaptcha["success"] != true)
    exit(MakeJsonOutput::errorOutput($recaptcha["error-codes"]));
elseif ($recaptcha["score"] < 0.5)
    exit(MakeJsonOutput::errorOutput([ "too-low-score" ]));

$connection = Database::connect();
if($connection === false)
    exit(MakeJsonOutput::errorOutput([ "no-database-connection" ]));

$result = mysqli_fetch_assoc($connection->query("SELECT * FROM accounts WHERE active=1 AND login='{$login}'"));
exit(MakeJsonOutput::successOutput($result["hash"]));