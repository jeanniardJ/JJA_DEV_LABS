<?php

namespace App\Tests\Unit\Service;

use App\Service\EmailService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailServiceTest extends TestCase
{
    public function testSendResponseEmail(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) {
                return $email->getTo()[0]->getAddress() === 'lead@test.com' &&
                       $email->getSubject() === 'Réponse à votre demande - JJA DEV LAB' &&
                       str_contains($email->getTextBody(), 'Ma réponse IA');
            }));

        $service = new EmailService($mailer, 'noreply@jjadevlab.com');
        $service->sendResponseEmail('lead@test.com', 'Ma réponse IA');
    }
}
