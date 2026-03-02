<?php

namespace App\Service;

use App\Entity\Appointment;
use App\Entity\Lead;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ConfigurationService $configService,
        private readonly string $adminEmail, // Fallback from services.yaml
        private readonly string $mailerDsn
    ) {
    }

    private function createBaseEmail(string $to, string $subject): Email
    {
        // On tente d'extraire l'expéditeur valide du DSN (ex: no-reply@jja-dev.fr)
        // car OVH refuse souvent d'envoyer avec un From différent de l'Auth User.
        $sender = $this->adminEmail;
        if (preg_match('/smtp:\/\/([^:]+):/', $this->mailerDsn, $matches)) {
            $sender = urldecode($matches[1]);
        }

        return (new Email())
            ->from($sender)
            ->to($to)
            ->subject($subject . ' - JJA DEV LAB');
    }

    public function sendResponseEmail(string $to, string $content): void
    {
        $email = $this->createBaseEmail($to, 'Réponse à votre demande')
            ->text($content);
        $this->mailer->send($email);
    }

    public function sendContactNotification(Lead $lead): void
    {
        $adminRecipient = $this->configService->get('admin_email', $this->adminEmail);
        
        $content = "NOUVEAU LEAD IDENTIFIÉ\n\n";
        $content .= "Nom : {$lead->getName()}\n";
        $content .= "Email : {$lead->getEmail()}\n";
        $content .= "Sujet : {$lead->getSubject()}\n";
        $content .= "Message :\n{$lead->getMessage()}\n\n";
        $content .= "Accédez au dashboard pour répondre : https://jja-dev.fr/admin";

        $email = $this->createBaseEmail($adminRecipient, 'Alerte : Nouveau Contact')
            ->text($content);
        
        $this->mailer->send($email);
    }

    public function sendContactAck(Lead $lead): void
    {
        $content = "Bonjour {$lead->getName()},\n\n";
        $content .= "Merci d'avoir contacté le JJA DEV LAB. Votre message a bien été transmis à notre noyau de traitement.\n";
        $content .= "Nous reviendrons vers vous dans les plus brefs délais pour répondre à votre demande.\n\n";
        $content .= "Récapitulatif de votre demande :\n";
        $content .= "Sujet : {$lead->getSubject()}\n";
        $content .= "Message : {$lead->getMessage()}\n\n";
        $content .= "Cordialement,\nL'équipe JJA DEV LAB";

        $email = $this->createBaseEmail($lead->getEmail(), 'Accusé de réception de votre message')
            ->text($content);
        
        $this->mailer->send($email);
    }

    public function sendAppointmentPending(Appointment $appointment): void
    {
        $lead = $appointment->getLead();
        $date = $appointment->getStartsAt()->format('d/m/Y à H:i');
        
        $content = "Bonjour {$lead->getName()},\n\n";
        $content .= "Votre demande de rendez-vous pour le {$date} a bien été reçue.\n";
        $content .= "Elle est actuellement en attente de confirmation par notre équipe.\n\n";
        $content .= "Vous recevrez un email dès que votre créneau sera validé.\n\nCordialement,\nL'équipe JJA DEV LAB";

        $email = $this->createBaseEmail($lead->getEmail(), 'Demande de rendez-vous reçue')
            ->text($content);
        $this->mailer->send($email);
    }

    public function sendAppointmentConfirmed(Appointment $appointment): void
    {
        $lead = $appointment->getLead();
        $date = $appointment->getStartsAt()->format('d/m/Y à H:i');
        
        $content = "Bonjour {$lead->getName()},\n\n";
        $content .= "Bonne nouvelle ! Votre rendez-vous est CONFIRMÉ pour le {$date}.\n";
        $content .= "La session se déroulera via Google Meet ou par téléphone.\n\n";
        $content .= "À très bientôt,\nL'équipe JJA DEV LAB";

        $email = $this->createBaseEmail($lead->getEmail(), 'Rendez-vous CONFIRMÉ')
            ->text($content);
        $this->mailer->send($email);
    }

    public function sendAppointmentRefused(Appointment $appointment): void
    {
        $lead = $appointment->getLead();
        $date = $appointment->getStartsAt()->format('d/m/Y à H:i');
        
        $content = "Bonjour {$lead->getName()},\n\n";
        $content .= "Nous sommes désolés, mais nous ne pouvons pas confirmer votre rendez-vous du {$date}.\n";
        $content .= "Nos créneaux ont été modifiés ou une indisponibilité technique est survenue.\n\n";
        $content .= "N'hésitez pas à choisir un autre créneau sur notre site.\n\nCordialement,\nL'équipe JJA DEV LAB";

        $email = $this->createBaseEmail($lead->getEmail(), 'Rendez-vous non disponible')
            ->text($content);
        $this->mailer->send($email);
    }
}
