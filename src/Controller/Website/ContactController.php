<?php

namespace App\Controller\Website;

use App\Entity\Lead;
use App\Enum\LeadStatus;
use App\Form\ContactType;
use App\Service\SystemLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em, SystemLogService $logService): Response
    {
        $lead = new Lead();
        $form = $this->createForm(ContactType::class, $lead);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lead->setStatus(LeadStatus::NEW);
            $em->persist($lead);
            $em->flush();

            $logService->success("Nouveau lead identifié via formulaire : " . $lead->getEmail(), "LEAD");

            $this->addFlash('success', 'Votre message a été transmis avec succès au laboratoire.');
            return $this->redirectToRoute('app_contact');
        }

        return $this->render('website/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
