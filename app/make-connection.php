<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/Database.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/make-output.php";

use Classes\Database;
use function Lib\makeOutput;

/**
 * Shortcut for connection to the database
 *
 * @return Database|void
 */
function makeDatabaseConnection () {
    $database = new Database();
    $connectionResult = $database->connectToDatabase();
    if (!$connectionResult) exit(makeOutput(false, [ "no-database-connection" ]));

    return $database;
}