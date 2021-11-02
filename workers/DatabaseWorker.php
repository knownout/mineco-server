<?php

namespace Workers;

require_once "../server-config.php";

use Types\MaterialsSearchOptions;
use mysqli;

class DatabaseWorker
{
    private mysqli $connection;
    public ?string $connection_error = null;

    protected function __construct ()
    {
        $this->connection = new mysqli(...array_values(DatabaseOptions));
        if ($this->connection->connect_error)
            $this->connection_error = $this->connection->connect_error;
    }

    /**
     * Method to get all displayed tags
     * @return array tags with display = 1 or null if connection error
     */
    public function getTagsList ()
    {
        if ($this->connection_error) return null;

        // Get all tags from database
        $result = $this->connection->query("SELECT name FROM tags WHERE display=1")->fetch_all();
        $tagsList = [];

        // Set tags as simple string array
        foreach ($result as $item)
            array_push($tagsList, $item[0]);

        return $tagsList;
    }

    /**
     * Search in database for material
     * @param MaterialsSearchOptions $searchOptions constructor for search options
     * @param int $limit if more than 0 limits count of selecting materials
     * @param array $columns specify columns to retrieve from db
     * @return mixed assoc array of the materials or null if connection error
     */
    public function getMaterialsMeta (MaterialsSearchOptions $searchOptions, int $limit = 0, array $columns = [])
    {
        $queryString = [];

        if ($this->connection_error) return null;

        // Parse options object

        // If identifiers list set, do not parse other options
        if (isset($searchOptions->identifiers))
        {
            foreach ($searchOptions->identifiers as $identifier)
                array_push($queryString, "identifier='{$identifier}'");
        } else
        {
            // Parse options without identifiers list
            if (isset($searchOptions->pinned))
                array_push($queryString, "pinned=" . (int)$searchOptions->pinned);

            if (isset($searchOptions->tag))
                array_push($queryString, "tags like '%{$searchOptions->tag}%'");

            if (isset($searchOptions->time_start))
                array_push($queryString, "time>={$searchOptions->time_start}");

            if (isset($searchOptions->time_end))
                array_push($queryString, "time<{$searchOptions->time_end}");

            if (isset($searchOptions->title))
                array_push($queryString, "title like '%{$searchOptions->title}%'");
        }

        // Specify column selector as * if other not provided by user
        $columnsList = "*";

        // Specify user-provided columns
        if(isset($columns) and count($columns) > 0) $columnsList = trim(join(", ", $columns));

        // Query base string
        $query = "SELECT {$columnsList} FROM materials";

        // Add options parse result to base query string
        if(count($queryString) > 1) $query = $query . " WHERE " . join(" AND ", $queryString);
        else if(count($queryString) == 1) $query = $query . " WHERE " . $queryString[0];

        // Order materials by time
        $query .= " ORDER BY time DESC";

        // If limit provided, set it
        if ($limit > 0) $query .= " LIMIT {$limit}";

        // Execute and return query
        return $this->connection->query(trim($query))->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get account data from database with account login
     * @param string $login user login
     * @return mixed|null
     */
    protected function getAccountData ($login)
    {
        if ($this->connection_error) return null;

        // Get specified account data from db
        $result = $this->connection->query("SELECT * FROM accounts WHERE login='{$login}'");

        // If account not exist, return null
        if (is_bool($result) || count((array) $result) < 1) return null;

        return $result[0];
    }
}