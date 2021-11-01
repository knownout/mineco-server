<?php

const DatabaseOptions = [
    "hostname" => "127.0.0.1",
    "username" => "root",
    "password" => "",
    "database" => "mineco",
    "port" => "3306"
];

global $MaterialsPath;
$MaterialsPath =  join(DIRECTORY_SEPARATOR, [$_SERVER["DOCUMENT_ROOT"], "user-content", ""]);