<?php

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require $_SERVER["DOCUMENT_ROOT"] . "/vendor/autoload.php";

/**
 * Class for sending emails through SMTP server
 */
class SendMail
{
    public PHPMailer $mail;
    public array $associations;
    private $authData;

    /**
     * @throws \Exception
     */
    public function __construct ()
    {
        $this->mail = new PHPMailer(true);
        $this->authData = $this->getAuthenticationData();
        $this->associations = $this->authData["associations"];
    }

    /**
     * @throws \Exception
     */
    private function getAuthenticationData ()
    {
        $path = $_SERVER["DOCUMENT_ROOT"] . "/mail-options.json";

        if (!file_exists($path)) throw new \Exception("No e-mail configuration file found ");
        return @json_decode(@file_get_contents($path), true);
    }

    public function sendMail (array $to, array $attachments, string $subject, string $body)
    {
        $mail = $this->mail;
        if (is_null($this->authData)) return false;

        try {
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host = $this->authData["smtp"];
            $mail->SMTPAuth = true;
            $mail->Username = $this->authData["address"];
            $mail->Password = $this->authData["password"];
            $mail->SMTPSecure = $this->authData["encryption"];
            $mail->Port = $this->authData["port"];

            //Recipients
            $mail->setFrom($this->authData["address"]);
            foreach ($to as $recipient) $mail->addAddress($recipient);

            //Attachments
            foreach ($attachments as $attachment) $mail->addAttachment($attachment, basename($attachment));

            //Content
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $mail->Body;

            $mail->send();
        } catch (Exception $exception) {
            return false;
        }

        return true;
    }
}