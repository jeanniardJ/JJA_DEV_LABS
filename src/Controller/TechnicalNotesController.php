<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TechnicalNotesController extends AbstractController
{
    #[Route('/technical/notes', name: 'app_technical_notes')]
    public function index(): Response
    {
        return $this->render('technical_notes/index.html.twig', [
            'controller_name' => 'TechnicalNotesController',
        ]);
    }
}
