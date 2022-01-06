<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/types/requests.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/Recaptcha.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/app/make-connection.php";

use Types\PostRequests;
use Classes\Recaptcha;

function authenticate() {
    $login = $_POST[ PostRequests::accountLogin ];
    $hash = $_POST[ PostRequests::accountHash ];
    $token = $_POST[ PostRequests::recaptchaToken ];

    if (is_null($login) or is_null($hash) or is_null($token)) return false;

    $recaptchaVerify = (new Recaptcha())->verifyScore($token);
    if (!$recaptchaVerify) return false;

    $database = makeDatabaseConnection();

    $query = $database->query("SELECT login,hash,fullname FROM accounts WHERE active=1 AND login='$login'");
    if (!$query) return false;

    $accountData = $query->fetch_assoc();
    if ($query->num_rows !== 1 or is_null($accountData) or !$accountData) return false;

    if ($accountData["hash"] !== $hash) return false;
    return $accountData;
}