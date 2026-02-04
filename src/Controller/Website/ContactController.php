<?php

namespace App\Controller\Website;

use App\Entity\Lead;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $lead = new Lead();
        $form = $this->createForm(ContactType::class, $lead);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($lead);
            $entityManager->flush();

            $this->addFlash('success', 'Merci pour votre message !');

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('website/contact.html.twig', [
            'form' => $form,
        ]);
    }
}
