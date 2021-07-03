<?php

namespace App\Service\Email;

class Email
{
    protected $transport;

    public function __construct(string $host, string $port, string $secure, strin $login, string $password)
    {
        $this->transport = (new \Swift_SmtpTransport($host, $port))
            ->setEncryption($secure)
            ->setUsername($login)
            ->setPassword($password);
    }

    public function send(string $from, string $to, string $subject, string $body, array $attachments = [])
    {
        $mailer = new \Swift_Mailer($this->transport);
        $message = new \Swift_Message();

        $message->setSubject($subject);
        $message->setFrom($from);
        $message->addTo($to);

        foreach ($attachments as $attachment)
        {
            $attachment = \Swift_Attachment::fromPath($attachment);
            $message->attach($attachment);
        }

        $message->addPart($body);
        $mailer->send($message);
    }
}