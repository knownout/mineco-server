<?php

namespace Lib;

function makeOutput (bool $success, $content): string {
    $response = array_merge([ "success" => $success ], $success
        ? [ "responseContent" => $content ]
        : [ "errorCodes" => $content ]);

    return json_encode($response, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
}

function useOutputHeader () {
    header('Content-Type: application/json; charset=utf-8');
}