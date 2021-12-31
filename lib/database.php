<?php

namespace Lib;

class Database
{
    public static function connect ()
    {
        $options = json_decode(file_get_contents("../../database-options.json"), true);
        return mysqli_connect(
            $options["hostname"],
            $options["username"],
            $options["password"],
            $options["database"]
        );
    }
}