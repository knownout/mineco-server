<?php

namespace Classes;

/**
 * Class for building paths to files and directories, also
 * provide some variables (like storage paths)
 */
class PathBuilder {
    public string $root;
    public string $fileStorage = DIRECTORY_SEPARATOR . "storage" . DIRECTORY_SEPARATOR . "files-storage";
    public string $materialsStorage = DIRECTORY_SEPARATOR . "storage" . DIRECTORY_SEPARATOR . "materials-storage";

    public function __construct () {
        $this->root = $_SERVER["DOCUMENT_ROOT"];

        $this->fileStorage = $_SERVER["DOCUMENT_ROOT"] . $this->fileStorage;
        $this->materialsStorage = $_SERVER["DOCUMENT_ROOT"] . $this->materialsStorage;

        if (!file_exists($_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "storage"))
            mkdir($_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "storage");

        if (!file_exists($this->fileStorage)) mkdir($this->fileStorage);
        if (!file_exists($this->materialsStorage)) mkdir($this->materialsStorage);
    }

    /**
     * Create system-independent path from the path fragments
     *
     * @param mixed ...$fragments path fragments
     * @return string system-independent path
     */
    public function makePath (...$fragments): string {
        $path = [];
        foreach ($fragments as $fragment) $path[] = $fragment;

        return join(DIRECTORY_SEPARATOR, $path);
    }
}