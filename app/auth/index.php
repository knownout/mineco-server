<?php

require_once "Auth.php";
require_once "../../types/requests.php";

use Processors\Auth;
use Types\Requests;

$auth = new Auth($_POST[ Requests::accountLogin ], $_POST[ Requests::accountHash ]);
$auth->verifyRecaptcha($_POST[ Requests::recaptchaToken ]);
$auth->verifyAccountData();