<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/types/requests.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/Recaptcha.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/app/make-connection.php";

use Types\Requests;
use Classes\Recaptcha;

/**
 * Function for verifying user account data with
 * the database entry
 *
 * @return array|false account data if verified or false
 */
function verifyAccountData() {
    // Get account data from POST requests
    $login = $_POST[ Requests::accountLogin ];
    $hash = $_POST[ Requests::accountHash ];
    $token = $_POST[ Requests::recaptchaToken ];

    // Check if all necessary data provided
    if (is_null($login) or is_null($hash) or is_null($token)) return false;

    // Verify recaptcha token
    $recaptchaVerify = (new Recaptcha())->verifyScore($token);
    if (!$recaptchaVerify) return false;

    // Connect to the database and require account data by login
    $database = makeDatabaseConnection();

    $query = $database->query("SELECT login,hash,fullname FROM accounts WHERE active=1 AND login='$login'");
    $database->mysqli->close();
    if (!$query) return false;


    // Compare database user hash with provided hash
    $accountData = $query->fetch_assoc();
    if ($query->num_rows !== 1 or is_null($accountData) or !$accountData) return false;

    if ($accountData["hash"] !== $hash) return false;
    return $accountData;
}