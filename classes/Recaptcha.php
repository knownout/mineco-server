<?php

namespace Classes;

require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/PathBuilder.php";

/**
 * Class for verification Google reCAPTCHA client tokens
 */
class Recaptcha {
    private ?string $secretKey = null;

    // Get Google reCAPTCHA secret key
    public function __construct () {
        $container = new \PathBuilder();
        $keysFileLocation = $container->makePath($container->root, "app", "auth", "recaptcha-keys.json");

        $keys = json_decode(file_get_contents($keysFileLocation), true);
        if (array_key_exists("secretKey", $keys))
            $this->secretKey = $keys["secretKey"];
    }

    /**
     * Verify user request by its score
     *
     * @param string|null $token request client token
     * @param float $minScore minimum request score to be accepted
     * @return bool verification result
     */
    public function verifyScore (?string $token, float $minScore = 0.5): bool {
        if (is_null($this->secretKey) or !isset($token)) return false;

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