<?php

namespace App\Controller\Admin;

use App\Entity\Lead;
use App\Service\EmailService;
use App\Service\GeminiAIService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/ai')]
#[IsGranted('ROLE_ADMIN')]
class AiAssistantController extends AbstractController
{
    #[Route('/generate/{id}', name: 'admin_ai_generate', methods: ['POST'])]
    public function generate(Lead $lead, GeminiAIService $geminiAIService): JsonResponse
    {
        // Anonymize email for privacy (RGPD)
        $emailParts = explode('@', $lead->getEmail());
        $maskedEmail = substr($emailParts[0], 0, 2) . '***@' . $emailParts[1];

        // Construct rich context
        $prompt = sprintf(
            "Nom du lead : %s
Email : %s (masqué pour confidentialité)
Sujet : %s
Message : %s
",
            $lead->getName(),
            $maskedEmail,
            $lead->getSubject(),
            $lead->getMessage()
        );

        // Add appointments if any
        if (!$lead->getAppointments()->isEmpty()) {
            $prompt .= "
Rendez-vous prévus :
";
            foreach ($lead->getAppointments() as $appointment) {
                $prompt .= sprintf("- %s
", $appointment->getStartsAt()->format('d/m/Y H:i'));
            }
        }

        try {
            $draft = $geminiAIService->generateResponse($prompt);
            return new JsonResponse(['draft' => $draft]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Impossible de générer le brouillon : ' . $e->getMessage()], 500);
        }
    }

    #[Route('/send/{id}', name: 'admin_ai_send', methods: ['POST'])]
    public function send(Lead $lead, Request $request, EmailService $emailService, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $content = $data['content'] ?? null;

        if (!$content) {
            return new JsonResponse(['error' => 'Contenu de l\'email manquant.'], 400);
        }

        try {
            $emailService->sendResponseEmail($lead->getEmail(), $content);
            
            return new JsonResponse(['message' => 'Email envoyé avec succès.']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Échec de l\'envoi de l\'email : ' . $e->getMessage()], 500);
        }
    }
}
