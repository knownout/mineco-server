<?php

namespace Workers;

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
     * Create JSON string in API output style
     * @param bool $success request state
     * @param mixed $meta answer data
     */
    public static function makeJsonOutput (bool $success, $meta)
    {
        return json_encode([
            "success" => $success,
            "meta" => $meta
        ], JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES + JSON_PRETTY_PRINT);
    }

    public static function returnJsonOutput (bool $success, $meta)
    {
        header('Content-Type: application/json; charset=utf-8');
        exit(StandardLibrary::makeJsonOutput($success, $meta));
    }
}