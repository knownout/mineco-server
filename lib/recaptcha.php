<?php

namespace Lib;

class Recaptcha
{
    private string $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function verify()
    {
        $keys = @json_decode(@file_get_contents("recaptcha-keys.json"), true);
        $data = [
            "secret" => $keys["secretKey"],
            "response" => $this->token
        ];

        $options = ["http" => [
            "header" => "Content-type: application/x-www-form-urlencoded\r\n",
            "method" => "POST",
            "content" => http_build_query($data)
        ]];

        $context = stream_context_create($options);
        return @json_decode(file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify", false, $context
        ), true);
    }
}