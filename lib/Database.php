<?php

namespace Lib;

/**
 * Class for improving work with mysql databases
 *
 * @author re-knownout
 */
class Database
{
    public \mysqli $connection;

    /**
     * Connect to database with options from database-options.json that contains
     * object with next fields: hostname, username, password, database
     */
    public function __construct ()
    {
        $options = json_decode(file_get_contents("../../database-options.json"), true);
        $this->connection = @new \mysqli(...array_values($options));
    }

    /**
     * Query content from database and parse response as associative array
     *
     * @param string $queryString query to database
     * @return array|false|null associative array (if presented)
     */
    public function queryData (string $queryString)
    {
        $query = $this->connection->query($queryString);
        return $query->fetch_assoc();
    }
}