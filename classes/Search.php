<?php

namespace Classes;

require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/QueryBuilder.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/Recaptcha.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/use-cors.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/lib/make-output.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/types/requests.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/app/make-connection.php";

use Types\PostRequests;


class Search {
    private QueryBuilder $queryBuilder;
    public function __construct (QueryBuilder $queryBuilder) {
        $this->queryBuilder = $queryBuilder;
    }

    public function requireResponse (bool $recaptcha = true) {
        if($recaptcha) {
            $recaptchaVerify = (new Recaptcha())->verifyScore($_POST[ PostRequests::recaptchaToken ]);
            if (!$recaptchaVerify) return false;
        }

        $database = makeDatabaseConnection();

        $query = $database->query($this->queryBuilder->query);
        if (!$query) return false;

        $response = [];
        for ($i = 0; $i < $query->num_rows; $i++) {
            $result = $query->fetch_assoc();
            $response[] = $result;
        }

        return $response;
    }
}