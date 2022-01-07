<?php

namespace Classes;

require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/QueryBuilder.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/Recaptcha.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/use-cors.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/make-output.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/types/requests.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/app/make-connection.php";

use Types\Requests;


/**
 * Class for creating and executing search
 * queries in the database
 */
class Search {
    private QueryBuilder $queryBuilder;

    public function __construct (QueryBuilder $queryBuilder) {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Method for searching with specified QueryBuilder
     * query in the database
     *
     * @param bool $recaptcha verify requests with Google reCAPTCHA token
     * @return array|false associative array or false if error on not found
     */
    public function execute (bool $recaptcha = true) {
        // Use recaptcha to verify request if enabled
        if($recaptcha) {
            $recaptchaVerify = (new Recaptcha())->verifyScore($_POST[ Requests::recaptchaToken ]);
            if (!$recaptchaVerify) return false;
        }

        // Connect to the database and send query
        $database = makeDatabaseConnection();

        $query = $database->query($this->queryBuilder->query);
        if (!$query) return false;

        $response = [];

        // Get response as assoc array
        for ($i = 0; $i < $query->num_rows; $i++) {
            $result = $query->fetch_assoc();
            $response[] = $result;
        }

        $database->mysqli->close();
        return $response;
    }
}