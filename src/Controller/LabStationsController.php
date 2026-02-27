<?php

namespace App\Controller;

use App\Entity\LabStation;
use App\Form\LabStationType;
use App\Repository\LabStationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/lab-stations')]
#[IsGranted('ROLE_ADMIN')]
final class LabStationsController extends AbstractController
{
    #[Route('', name: 'app_lab_station_index')]
    public function index(LabStationRepository $repository): Response
    {
        return $this->render('lab_stations/index.html.twig', [
            'stations' => $repository->findAllSorted(),
        ]);
    }

    #[Route('/new', name: 'app_lab_station_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, LabStationRepository $repository): Response
    {
        $labStation = new LabStation();
        $labStation->setPosition($repository->count([]) + 1);

        $form = $this->createForm(LabStationType::class, $labStation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($labStation);
            $entityManager->flush();

            $this->addFlash('success', 'Station créée avec succès.');
            return $this->redirectToRoute('app_lab_station_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lab_stations/edit.html.twig', [
            'station' => $labStation,
            'form' => $form->createView(),
            'is_new' => true,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_lab_station_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LabStation $labStation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LabStationType::class, $labStation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Station mise à jour.');
            return $this->redirectToRoute('app_lab_station_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lab_stations/edit.html.twig', [
            'station' => $labStation,
            'form' => $form->createView(),
            'is_new' => false,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_lab_station_delete', methods: ['POST'])]
    public function delete(Request $request, LabStation $labStation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $labStation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($labStation);
            $entityManager->flush();
            $this->addFlash('success', 'Station supprimée.');
        }

        return $this->redirectToRoute('app_lab_station_index', [], Response::HTTP_SEE_OTHER);
    }
}
