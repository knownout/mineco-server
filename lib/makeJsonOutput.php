<?php

namespace Lib;

class MakeJsonOutput
{
    public static function errorOutput (array $errorCodes)
    {
        return json_encode([
            "success" => false,
            "errorCodes" => $errorCodes
        ]);
    }

    public static function successOutput (array $result)
    {
        return json_encode([
            "success" => true,
            "responseContent" => $result
        ]);
    }
}