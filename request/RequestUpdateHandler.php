<?php

require_once "../types/RequestTypesList.php";
require_once "../workers/AccountsWorker.php";
require_once "../workers/StandardLibrary.php";
require_once "../workers/Logger.php";
require_once "../server-config.php";

use Types\RequestTypesList;
use Workers\AccountsWorker;
use Workers\StandardLibrary;
use Workers\Logger;


class RequestUpdateHandler extends AccountsWorker
{
    private Logger $logger;

    /**
     * Verify is account data is valid
     * @return array verification result
     */
    private function verifyAuthentication ()
    {
        $login = RequestHeadersHandler::getPostRequest(RequestTypesList::AccountLogin);
        $hash = RequestHeadersHandler::getPostRequest(RequestTypesList::AccountHash);

        if (is_null($login) or is_null($hash)) return [false, $login, $hash];
        return [parent::compareAccountData($login, $hash), $login, $hash];
    }

    public function __construct ()
    {
        parent::__construct();
        $this->logger = new Logger();
    }

    /**
     * Change account password in database (auth required)
     */
    public function changePassword ()
    {
        // Get data from $_POST request
        $newHash = RequestHeadersHandler::getPostRequest(RequestTypesList::AccountNewHash);
        [$verification, $login, $hash] = $this->verifyAuthentication();

        // Check if authentication data provided
        if (!$verification)
            StandardLibrary::returnJsonOutput(false, "auth data invalid");

        // Check if new password provided and if it is an md5 hash
        if (is_null($newHash) or strlen($newHash) != 32)
            StandardLibrary::returnJsonOutput(false, "new password hash not specified or invalid");


        // Send request to database (this method compares auth data with database)
        $result = parent::changeAccountPassword($login, $hash, $newHash);

        if (!$result) StandardLibrary::returnJsonOutput(false, "password not changed");

        $this->logger->saveAction(Logger::PasswordChange, $login, $login);
        StandardLibrary::returnJsonOutput(true, "password changed");
    }

    /**
     * Update material db entry (for time, title, identifier and tags) and
     * files on drive (content file, preview, images, files)
     * @return bool update result
     */
    public function updateMaterial ()
    {
        $identifier = RequestHeadersHandler::getPostRequest(RequestTypesList::UpdateIdentifier);
        $content = RequestHeadersHandler::getPostRequest(RequestTypesList::UpdateContent);
        [$verification, $login, $hash] = $this->verifyAuthentication();

        if (!$verification) StandardLibrary::returnJsonOutput(false, "auth data invalid");

        // TODO: implement update method
        StandardLibrary::returnJsonOutput(false, "update method not implemented");
        return false;
    }

    /**
     * Fully remove material from drive and database
     *
     * Warning! Method recursively remove data from material root folder
     */
    public function removeMaterial ()
    {
        $identifier = RequestHeadersHandler::getPostRequest(RequestTypesList::UpdateIdentifier);
        [$verification, $login] = $this->verifyAuthentication();

        if (!$verification) StandardLibrary::returnJsonOutput(false, "auth data invalid");

        global $MaterialsPath;
        $materialPath = $MaterialsPath . $identifier . DIRECTORY_SEPARATOR;

        $this->connection->query("DELETE FROM materials WHERE identifier='{$identifier}'");
        if(!is_dir($materialPath)) StandardLibrary::returnJsonOutput(false, "material not found");

        try
        {
            $it = new RecursiveDirectoryIterator($materialPath, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it,
                RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($files as $file)
            {
                if ($file->isDir()) rmdir($file->getRealPath());
                else unlink($file->getRealPath());
            }

            $this->logger->saveAction(Logger::MaterialRemove, $login, $identifier);
        } catch (Exception $e)
        {
            StandardLibrary::returnJsonOutput(false, "recursive files remove error");
        }

        $removeDirectory = rmdir($materialPath);
        StandardLibrary::returnJsonOutput(
            $removeDirectory, $removeDirectory ?
            "material removed" :
            "material root folder not removed"
        );
    }
}