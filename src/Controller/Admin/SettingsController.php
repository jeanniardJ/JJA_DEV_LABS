<?php

namespace App\Controller\Admin;

use App\Entity\AppConfig;
use App\Form\AppConfigType;
use App\Repository\AppConfigRepository;
use App\Service\SystemLogService;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/settings')]
#[IsGranted('ROLE_ADMIN')]
class SettingsController extends AbstractController
{
    #[Route('', name: 'admin_settings_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        AppConfigRepository $configRepository, 
        Connection $connection,
        EntityManagerInterface $em,
        SystemLogService $logService
    ): Response {
        $configs = $configRepository->findAll();
        
        // Formulaires pour chaque config (approche simple pour ce Lab)
        // Note: En prod on ferait une collection, mais ici on veut rester granulaire
        
        $editId = $request->query->get('edit');
        $editForm = null;
        if ($editId) {
            $configToEdit = $configRepository->find($editId);
            if ($configToEdit) {
                $form = $this->createForm(AppConfigType::class, $configToEdit);
                $form->handleRequest($request);
                
                if ($form->isSubmitted() && $form->isValid()) {
                    $em->flush();
                    $logService->warning("Réglage système modifié : " . $configToEdit->getSettingKey() . " (Nouvelle valeur: " . $configToEdit->getSettingValue() . ")", "CONFIG");
                    $this->addFlash('success', 'Configuration mise à jour.');
                    return $this->redirectToRoute('admin_settings_index', [], Response::HTTP_SEE_OTHER);
                }
                $editForm = $form->createView();
            }
        }

        return $this->render('admin/settings/index.html.twig', [
            'configs' => $configs,
            'db_status' => $this->checkDatabase($connection),
            'php_version' => PHP_VERSION,
            'env' => $_ENV['APP_ENV'] ?? 'n/a',
            'edit_form' => $editForm,
            'edit_id' => $editId
        ]);
    }

    private function checkDatabase(Connection $connection): bool
    {
        try {
            $connection->executeQuery('SELECT 1');
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
