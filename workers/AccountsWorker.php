<?php

namespace Workers;

use mysqli_result;

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
    protected function compareAccountData (string $login, string $hash)
    {
        $account = parent::getAccountData($login);
        if (is_null($account) or $account["hash"] != $hash) return false;
        else return true;
    }

    /**
     * Change user account password with security check
     * @param string $login account login
     * @param string $hash account current (old) password
     * @param string $newHash new account password
     * @return bool|mysqli_result false if auth or request failed
     */
    protected function changeAccountPassword (string $login, string $hash, string $newHash)
    {
        $auth = $this->compareAccountData($login, $hash);
        if (!$auth) return false;

        $newHash = $this->connection->real_escape_string($newHash);
        $login = $this->connection->real_escape_string($login);

        $query = "UPDATE accounts SET hash='{$newHash}' WHERE login='{$login}' LIMIT 1";
        return $this->connection->query($query);
    }
}