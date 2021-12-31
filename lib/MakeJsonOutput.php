<?php

namespace Lib;

/**
 * Class for generating formatted json strings
 *
 * @author re-knownout
 */
class MakeJsonOutput
{
    /**
     * Set headers and encode array to json string
     *
     * @param array $output array of parameters
     * @return false|string json string
     */
    private static function useOutputFormat (array $output)
    {
        header('Content-Type: application/json');
        return json_encode($output, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
    }

    /**
     * Method for creating error messages
     *
     * @param array $errorCodes error codes (strings)
     * @return false|string formatted json string
     */
    public static function errorOutput (array $errorCodes)
    {
        return self::useOutputFormat([
            "success" => false,
            "errorCodes" => $errorCodes
        ]);
    }

    /**
     * Method for creating success messages
     *
     * @param array $result response content
     * @return false|string formatted json string
     */
    public static function successOutput (array $result)
    {
        return self::useOutputFormat([
            "success" => true,
            "responseContent" => $result
        ]);
    }

    /**
     * Method for creating non-formatted array of properties
     *
     * @param bool $success response result state
     * @param array $content response content
     * @return array array of properties
     */
    public static function makeRawOutput (bool $success, array $content): array
    {
        return array_merge([ "success" => $success ], $success
            ? [ "responseContent" => $content ]
            : [ "errorCodes" => $content ]);

    }
}