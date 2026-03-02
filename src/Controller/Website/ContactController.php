<?php

namespace App\Controller\Website;

use App\Entity\Lead;
use App\Enum\LeadStatus;
use App\Form\ContactType;
use App\Service\EmailService;
use App\Service\SystemLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em, SystemLogService $logService, EmailService $emailService): Response
    {
        $lead = new Lead();
        // Captcha activé partout (local 127.0.0.1 autorisé dans Cloudflare)
        $form = $this->createForm(ContactType::class, $lead, [
            'enable_captcha' => true
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lead->setStatus(LeadStatus::NEW);
            $em->persist($lead);
            $em->flush();

            // Notification Système
            $logService->success("Nouveau lead identifié via formulaire : " . $lead->getEmail(), "LEAD");

            // Envoi des emails de notification et confirmation
            try {
                $emailService->sendContactNotification($lead); // Pour l'admin
                $emailService->sendContactAck($lead);          // Pour le lead
            } catch (\Exception $e) {
                $logService->error("Échec de l'envoi des emails de contact : " . $e->getMessage(), "MAIL");
            }

            $this->addFlash('success', 'Votre message a été transmis avec succès au laboratoire.');
            
            return $this->redirectToRoute('app_contact', [], Response::HTTP_SEE_OTHER);
        }

        $responseCode = $form->isSubmitted() && !$form->isValid() 
            ? Response::HTTP_UNPROCESSABLE_ENTITY 
            : Response::HTTP_OK;

        return $this->render('website/contact.html.twig', [
            'form' => $form->createView(),
        ], new Response(null, $responseCode));
    }
}
