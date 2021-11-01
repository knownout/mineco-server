<?php

require_once "server-config.php";

use Types\MaterialsSearchOptions;
use Workers\AccountsWorker;
use Workers\DatabaseWorker;
use Workers\StandardLibrary;

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

        StandardLibrary::returnJsonOutput(...$this->makeOutputArray(
            parent::getMaterialsMeta($options, 1)
        ));
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

        StandardLibrary::returnJsonOutput(...$this->makeOutputArray(
            parent::getMaterialsMeta($options, $limit),
            0));
    }

    // TODO: add method to get latest news with count getLatestNews($count, $excludePinned = true)

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