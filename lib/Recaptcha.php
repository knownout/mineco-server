<?php

namespace Lib;

/**
 * Class for Google reCAPTCHA processing
 *
 * @author re-knownout
 */
class Recaptcha
{
    /**
     * Default minimum score
     */
    public const defaultMinScore = 0.5;

    /**
     * Common error message when request score too low
     */
    public const errorLowScore = "too-low-score";


    /**
     * Method for verifying client token with Google reCAPTCHA api
     *
     * @param string|null $token client-side token
     * @return array verification result
     */
    public static function verify (?string $token): array
    {
        if (is_null($token)) return MakeJsonOutput::makeRawOutput(false, ["no-client-token"]);
        $keys = json_decode(file_get_contents("recaptcha-keys.json"), true);
        $data = [
            "secret" => $keys["secretKey"],
            "response" => $token
        ];

        $options = ["http" => [
            "header" => "Content-type: application/x-www-form-urlencoded\r\n",
            "method" => "POST",
            "content" => http_build_query($data)
        ]];

        $context = stream_context_create($options);
        return json_decode(file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify", false, $context
        ), true);
    }

    /**
     * Method for creating data array from the Recaptcha::verify() method output
     *
     * @param array $verificationResult verification result
     * @return array data array
     */
    public static function getArray (array $verificationResult): array
    {
        return [$verificationResult["success"], $verificationResult["score"], $verificationResult["error-codes"]];
    }
}