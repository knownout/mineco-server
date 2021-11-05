<?php

namespace Controllers;

require_once "../server-config.php";

class StandardLibrary
{
    /**
     * Generate new identifier with 10 random symbols
     * @return string almost-unique identified
     */
    public static function makeIdentifier ()
    {
        return str_shuffle(substr(str_repeat(md5(mt_rand()), 2 + 10 / 32), 0, 10));
    }

    /**
     * Runs makeJsonOutput, then set content header and exit
     * @param bool $success request state
     * @param mixed $meta answer data
     */
    public static function returnJsonOutput (bool $success, $meta)
    {
        StandardLibrary::setCorsHeaders();
        header('Content-Type: application/json; charset=utf-8');
        exit(StandardLibrary::makeJsonOutput($success, $meta));
    }

    public static function setCorsHeaders ()
    {
        global $AllowAllOrigins;
        $protocol = isset($_SERVER["HTTPS"]) ? "https" : "http";

        if ($AllowAllOrigins) $origin = "*";
        else $origin = $protocol . "://" . $_SERVER["HTTP_HOST"];

        // Allow from any origin
        if (isset($_SERVER['HTTP_HOST']))
        {
            // Decide if the origin in $_SERVER['HTTP_HOST'] is one
            // you want to allow, and if so:
            header("Access-Control-Allow-Origin: {$origin}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
        {

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                // may also be using PUT, PATCH, HEAD etc
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

            exit(0);
        }
    }

    /**
     * Create JSON string in API output style
     * @param bool $success request state
     * @param mixed $meta answer data
     * @return false|string json output
     */
    public static function makeJsonOutput (bool $success, $meta)
    {
        return FileController::encodeJsonString([
            "success" => $success,
            "meta" => $meta
        ]);
    }
}