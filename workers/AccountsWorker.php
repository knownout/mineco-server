<?php

namespace Workers;

class AccountsWorker extends DatabaseWorker
{
    public function __construct ()
    {
        parent::__construct();
    }

    /**
     * Compare given account data with database entry
     * @param string $login user login
     * @param string $hash password md5 hash
     * @return bool compare result
     */
    public function compareAccountData (string $login, string $hash)
    {
        $account = parent::getAccountData($login);
        if (!$account or $account["hash"] != $hash) return false;
        else return true;
    }
}