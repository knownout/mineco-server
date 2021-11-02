<?php

namespace Workers;

require_once "../server-config.php";
require_once "../types/MaterialsSearchOptions.php";

require_once "DatabaseWorker.php";
require_once "AccountsWorker.php";
require_once "StandardLibrary.php";

use Types\MaterialsSearchOptions;

class MaterialsRequest extends DatabaseWorker
{
    private AccountsWorker $accountsWorker;

    /**
     * Generate arguments for returnJsonOutput method
     * @param mixed $result result of the db query
     * @param int $index if > -1 return only specified entry
     * @return array arguments for returnJsonOutput method
     */
    private function makeOutputArray ($result, $index = -1)
    {
        if (is_null($result)) $output = [false, "connection error"];
        else if (count($result) < 1) $output = [false, "no materials"];
        else $output = [true, $index < 0 ? $result : $result[$index]];

        return $output;
    }

    public function __construct ()
    {
        parent::__construct();

        $this->accountsWorker = new AccountsWorker();
    }

    /**
     * Get one or more pinned materials,
     * returns default JSON output
     */
    public function requestPinnedMaterial ()
    {
        $options = new MaterialsSearchOptions();
        $options->pinned = true;
        $options->tag = "Новости";

        $pinned = parent::getMaterialsMeta($options, 1, ["identifier", "title", "time"]);
        if (count($pinned) < 1) return null;
        else return $pinned[0];
    }

    /**
     * Get one or more materials by identifier
     * @param array $identifiers list of identifiers
     * @param int $limit max materials count
     */
    public function requestMaterialsByIdentifier (array $identifiers, int $limit = 0)
    {
        $options = new MaterialsSearchOptions();
        $options->identifiers = $identifiers;

        // FIXME: make this return value, not exit script
        StandardLibrary::returnJsonOutput(...$this->makeOutputArray(
            parent::getMaterialsMeta($options, $limit),
            0));
    }

    /**
     * Get latest materials from database (sorted descending by time)
     * @param MaterialsSearchOptions $options
     * @param int $limit count of news to get
     * @return mixed|null associative array of news
     */
    public function requestLatestMaterials (MaterialsSearchOptions $options, ?int $limit = 10)
    {

        // Get materials from database through db worker
        $materials = parent::getMaterialsMeta($options, is_int($limit) ? $limit : 10);

        // Return null if no materials found
        if (count($materials) < 1) return null;
        else return $materials;
    }
}