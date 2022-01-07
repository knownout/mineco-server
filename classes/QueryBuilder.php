<?php

namespace Classes;

/**
 * Class for building MySQL queries from scratch
 */
class QueryBuilder {
    public string $query;
    private int $_queries_count = 0;

    /**
     * Specify table name and filed set
     *
     * @param string $tableName name of the table
     * @param array $fieldsName list of fields to obtain
     */
    public function __construct (string $tableName = "materials", array $fieldsName = [ "*" ]) {
        $fields = join(",", $fieldsName);
        $this->query = "select $fields from $tableName";
    }

    /**
     * Directly add query to the class query string
     *
     * @param string $query query string without AND
     * @return $this
     */
    public function addQuery (string $query): QueryBuilder {
        $this->query = $this->query . ($this->_queries_count == 0 ? " where " : " and ") . $query;
        $this->_queries_count += 1;

        return $this;
    }

    /**
     * Add query to the class query string from the $_POST object. Automatically determine
     * key value type based on the key comparison signs (> or < for int only)
     *
     * @param string $queryStart field name and search option ("datetime" >= or "title like")
     * @param string $key POST object key (you must add % when using LIKE requests: %find:materialTitle%)
     * @return $this
     */
    public function addFromPost (string $queryStart, string $key): QueryBuilder {
        // Get value from the $_POST object
        $value = $_POST[ str_replace("%", "", $key) ];

        // Check if value exist
        if (!isset($value)) return $this;

        // Use percents chars if provided
        if (substr($key, -1) == "%") $value = $value . "%";
        if ($key[0] == "%") $value = "%" . $value;

        // Use value as int if comparison chars provided
        if (strpos($queryStart, ">") === false and strpos($queryStart, "<") === false)
            $value = "'$value'";

        return $this->addQuery($queryStart . " $value");
    }

    /**
     * Set items order for the query
     *
     * @param string $column soring column
     * @param string $type sorting type (desc or asc)
     * @return QueryBuilder
     */
    public function orderBy (string $column, string $type = "desc"): QueryBuilder {
        $this->query .= " order by $column $type";
        return $this;
    }

    /**
     * Set items limit for the query (client default - probably 30)
     *
     * @param int $limit number of maximum items
     * @param int $offset limit offset
     * @return QueryBuilder self
     */
    public function setLimit (int $limit, int $offset = 0): QueryBuilder {
        $this->query .= " limit $offset, $limit";
        return $this;
    }

    /**
     * Set items limit for the query from POST request
     *
     * @param string $key POST request key
     * @return QueryBuilder self
     */
    public function setLimitFromPost (string $key): QueryBuilder {
        $value = intval($_POST[$key]);
        if(!isset($value) or !$value or $value > 200)
            $this->setLimit(200);
        else $this->setLimit($value);

        return $this;
    }
}