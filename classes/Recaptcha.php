<?php

namespace Classes;

class Recaptcha {
    private ?string $secretKey = null;

    public function __construct () {
        $keys = json_decode(file_get_contents("recaptcha-keys.json"), true);
        if (array_key_exists("secretKey", $keys))
            $this->secretKey = $keys["secretKey"];
    }

    public function verifyScore (string $token, $minScore = 0.5): bool {
        if (is_null($this->secretKey)) return false;

        $data = [
            "secret" => $this->secretKey,
            "response" => $token
        ];

        $options = [ "http" => [
            "header" => "Content-type: application/x-www-form-urlencoded\r\n",
            "method" => "POST",
            "content" => http_build_query($data)
        ] ];

        $context = stream_context_create($options);
        $response = json_decode(file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify", false, $context
        ), true);

        if ($response["success"] and $response["score"] >= $minScore) return true;
        return false;
    }
}