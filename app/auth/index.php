<?php

/**
 * Endpoint for user authentication (account data verification)
 *
 * Returns common json output with account data
 * if verification successful
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/types/requests.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/make-output.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/use-cors.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/app/verify-account-data.php";

use function Lib\useOutputHeader;
use function Lib\makeOutput;
use function Lib\useCorsHeaders;

useCorsHeaders();
useOutputHeader();

$accountData = verifyAccountData();
if(!$accountData) exit(makeOutput(false, [ "auth-failed" ]));

exit(makeOutput(true, $accountData));

