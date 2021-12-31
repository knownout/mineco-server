<?php

namespace Processors;

require_once "../../lib/Cors.php";
require_once "../../lib/Recaptcha.php";
require_once "../../lib/MakeJsonOutput.php";
require_once "../../lib/Database.php";

use Lib\Recaptcha;
use Lib\MakeJsonOutput;
use Lib\Database;

class Auth
{
    private string $login;
    private string $hash;

    public function __construct ($login, $hash)
    {
        if (is_null($login) or is_null($hash)) exit(MakeJsonOutput::errorOutput([ "no-auth-data" ]));

        $this->login = $login;
        $this->hash = $hash;
    }

    public function verifyRecaptcha ($token)
    {
        [ $success, $score, $errorCodes ] = Recaptcha::getArray(Recaptcha::verify($token));

        if ($success != true) exit(MakeJsonOutput::errorOutput($errorCodes));
        elseif ($score < Recaptcha::defaultMinScore) exit(MakeJsonOutput::errorOutput([ Recaptcha::errorLowScore ]));
    }

    public function verifyAccountData ()
    {
        $db = @new Database();
        $account = $db->queryData(
            "SELECT login,hash,fullname FROM accounts WHERE active=1 AND login='$this->login'"
        );

        if (is_null($account)) exit(MakeJsonOutput::errorOutput([ "invalid-account" ]));

        if ($account["hash"] === $this->hash) exit(MakeJsonOutput::successOutput($account));
        exit(MakeJsonOutput::errorOutput([ "invalid-account-password" ]));
    }
}