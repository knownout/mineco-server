<?php

namespace Lib;

/**
 * Function for creating formatted encoded objects
 *
 * @param bool $success response result
 * @param mixed $content response content
 * @return string
 */
function makeOutput (bool $success, $content): string {
    $response = array_merge([ "success" => $success ], $success
        ? [ "responseContent" => $content ]
        : [ "errorCodes" => $content ]);

    return json_encode($response, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
}

/**
 * Function for attaching json content type to the output
 *
 * @return void
 */
function useOutputHeader () {
    header('Content-Type: application/json; charset=utf-8');
}