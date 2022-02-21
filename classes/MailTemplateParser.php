<?php

namespace Classes;

/**
 * Class for parsing mail template file
 */
class MailTemplateParser
{
    public string $template;
    public string $title;

    public function __construct ()
    {
        $this->template = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/mail-template.txt");
        $this->template = str_replace("\n", "<br />", $this->template);
    }

    public function addTitle (string $title)
    {
        $this->template = str_replace("#{message-title}", $title, $this->template);
        $this->title = $title . " при помощи формы обратной связи";
    }

    public function addUserData (string $name, string $address, string $phone, string $email)
    {
        $template = $this->template;

        $template = str_replace("#{name}", $name, $template);
        $template = str_replace("#{address}", $address, $template);
        $template = str_replace("#{phone}", $phone, $template);
        $template = str_replace("#{email}", $email, $template);

        $this->template = $template;
    }

    public function addMessageText (string $text)
    {
        $this->template = str_replace("#{message-text}", $text, $this->template);
    }

    public function addSubject (string $subject)
    {
        $this->template = str_replace("#{message-subject}", $subject, $this->template);
    }
}