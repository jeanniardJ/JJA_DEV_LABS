<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $adminEmail
    ) {
    }

    public function sendResponseEmail(string $to, string $content): void
    {
        $email = (new Email())
            ->from($this->adminEmail)
            ->to($to)
            ->subject('Réponse à votre demande - JJA DEV LAB')
            ->text($content);

        $this->mailer->send($email);
    }
}
