<?php

namespace Workers;

require_once "../server-config.php";
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
     *                READ-ONLY SECTION
     */

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
     * @param string $tag tag of materials, if not set will be default news tag
     * @param bool $find_pinned if true, answer may contain pinned articles too
     * @param int $limit count of news to get
     * @return mixed|null associative array of news
     */
    public function requestLatestMaterials (string $tag, bool $find_pinned = false, int $limit = 10)
    {
        // Setup options for search
        $options = new MaterialsSearchOptions();
        $options->tag = $tag;
        $options->pinned = $find_pinned ? null : false;

        // Get materials from database through db worker
        $materials = parent::getMaterialsMeta($options, $limit, $find_pinned ? [] :
            ["identifier", "title", "time", "tags"]);

        // Return null if no materials found
        if (count($materials) < 1) return null;
        else return $materials;
    }

    /**
     *                WRITE-ALLOWED SECTION
     */

    /**
     * @param string $login
     * @param string $hash
     * @param string $identifier
     * @param $content
     * @param $files_map
     * @param $images_map
     */
    public function updateMaterial (string $login, string $hash, string $identifier,
                                    $content, $files_map, $images_map)
    {
        $access = $this->accountsWorker->compareAccountData($login, $hash);
        if (!$access) StandardLibrary::returnJsonOutput(false, "invalid account");
        else
        {
            global $MaterialsPath;
            if (!is_dir($MaterialsPath . $identifier)) mkdir($MaterialsPath . $identifier);

            $path = $MaterialsPath . $identifier . DIRECTORY_SEPARATOR;
            file_put_contents($path . "content.json", $content);

            // TODO: files and images upload with $_POST and $_FILE request
        }
    }
}