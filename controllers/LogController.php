<?php

namespace Controllers;

require_once "DatabaseController.php";

/**
 * Class for logging actions like password change, material update & remove
 * @package Workers
 */
class LogController extends DatabaseController
{
    public const PasswordChange = 0;
    public const MaterialUpdate = 1;
    public const MaterialRemove = 2;

    public function __construct ()
    {
        parent::__construct();
    }

    /**
     * Saves action to the database
     * @param int $action action type (LogController::Constant)
     * @param string $login user login
     * @param string $affect affected object identifier (login for account)
     */
    public function saveAction (int $action, string $login, string $affect)
    {
        $actionString = "";
        $affect = $this->connection->real_escape_string($affect);
        $login = $this->connection->real_escape_string($login);

        switch ($action)
        {
            case 0:
                $actionString = "password";
                break;

            case 1:
                $actionString = "update";
                break;

            case 2:
                $actionString = "remove";
                break;
        }

        if(strlen($actionString) > 1) $this->connection->query(
            "INSERT INTO logs (login, action, affect) VALUES ('{$login}','{$actionString}','{$affect}')"
        );
    }
}