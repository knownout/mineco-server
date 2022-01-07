<?php

namespace Classes;

/**
 * Class for creating connection to the database using
 * file with hostname, password, etc
 */
class Database {
    public \mysqli $mysqli;
    public function connectToDatabase (): bool {
        $options = json_decode(file_get_contents(dirname(__DIR__) . "/database-options.json"), true);
        $this->mysqli = new \mysqli(...array_values($options));

        if($this->mysqli->errno) return false;
        return true;
    }

    public function query (string $query) {
        return $this->mysqli->query($query);
    }
}