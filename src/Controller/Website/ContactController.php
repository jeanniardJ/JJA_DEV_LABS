<?php

namespace App\Controller\Website;

use App\Entity\Lead;
use App\Enum\LeadStatus;
use App\Service\SystemLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods: ['POST'])]
    public function index(Request $request, EntityManagerInterface $em, SystemLogService $logService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $lead = new Lead();
        $lead->setName($data['name'] ?? 'Anonyme');
        $lead->setEmail($data['email'] ?? '');
        $lead->setSubject($data['subject'] ?? 'Contact');
        $lead->setMessage($data['message'] ?? '');
        $lead->setStatus(LeadStatus::NEW);

        $em->persist($lead);
        $em->flush();

        $logService->success("Nouveau lead identifié : " . $lead->getEmail(), "LEAD");

        return new JsonResponse(['status' => 'success', 'message' => 'Message envoyé avec succès']);
    }
}
