<?php

namespace Workers;

require_once "server-config.php";

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
    protected function getTagsList ()
    {
        if ($this->connection_error) return null;
        $result = $this->connection->query("SELECT * FROM tags WHERE display=1")->fetch_all();
        $tagsList = [];

        foreach ($result as $item)
        {
            array_push($tagsList, $item[1]);
        }

        return $tagsList;
    }

    /**
     * Search in database for material
     * @param MaterialsSearchOptions $searchOptions constructor for search options
     * @param int $limit if more than 0 limits count of selecting materials
     * @return mixed assoc array of the materials or null if connection error
     */
    protected function getMaterialsMeta (MaterialsSearchOptions $searchOptions, int $limit = 0)
    {
        $queryString = [];

        if ($this->connection_error) return null;
        if (isset($searchOptions->identifiers))
        {
            foreach ($searchOptions->identifiers as $identifier)
                array_push($queryString, "identifier='{$identifier}'");
        } else
        {
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

        $query = trim("SELECT * FROM materials WHERE " . join(" AND WHERE ", $queryString));
        if ($limit > 0) $query .= " LIMIT {$limit}";

        return $this->connection->query($query)->fetch_all();
    }

    protected function getAccountData ($login)
    {
        if ($this->connection_error) return null;
        $result = $this->connection->query("SELECT * FROM accounts WHERE login='{$login}'");
        if (count($result) < 1) return null;

        return $result[0];
    }
}