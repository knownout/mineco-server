<?php

namespace Controllers;

require_once "../server-config.php";
require_once "../types/MaterialSearchOptions.php";

require_once "DatabaseController.php";
require_once "AccountsController.php";
require_once "StandardLibrary.php";

use Types\MaterialSearchOptions;

class MaterialRequestController extends DatabaseController
{
    private AccountsController $accountsWorker;

    public function __construct ()
    {
        parent::__construct();

        $this->accountsWorker = new AccountsController();
    }

    /**
     * Get one or more pinned materials,
     * returns default JSON output
     */
    public function requestPinnedMaterial ()
    {
        $options = new MaterialSearchOptions();
        $options->pinned = true;
        $options->tag = "Новости";

        $pinned = parent::getMaterialsMeta($options, 1, [ "identifier", "title", "time" ]);
        if (count($pinned) < 1) return null;
        else return $pinned[0];
    }

    /**
     * Get one or more materials by identifier
     * @param string $identifier list of identifiers
     * @return mixed|null null if resulting array length contain nothing
     */
    public function requestMaterialByIdentifier (string $identifier)
    {
        $options = new MaterialSearchOptions();
        $options->identifier = $identifier;

        $result = parent::getMaterialsMeta($options, 1);

        if (count($result) > 0) return $result[0];
        else return null;
    }

    /**
     * Get latest materials from database (sorted descending by time)
     * @param MaterialSearchOptions $options
     * @param int $limit count of news to get
     * @return mixed|null associative array of news
     */
    public function requestMaterials (MaterialSearchOptions $options, ?int $limit = 10)
    {

        // Get materials from database through db worker
        $materials = parent::getMaterialsMeta($options, is_int($limit) ? $limit : 10);

        // Return null if no materials found
        if (count($materials) < 1) return null;
        else return $materials;
    }
}