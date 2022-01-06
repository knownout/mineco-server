<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/types/requests.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/make-output.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/use-cors.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/app/authenticate.php";

use function Lib\useOutputHeader;
use function Lib\makeOutput;
use function Lib\useCorsHeaders;

useCorsHeaders();
useOutputHeader();

$accountData = authenticate();
if(!$accountData) exit(makeOutput(false, [ "auth-failed" ]));

exit(makeOutput(true, $accountData));

