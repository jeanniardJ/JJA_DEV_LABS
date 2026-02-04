<?php

namespace App\Controller\Api;

use App\Service\SiteVerificationService;
use App\Message\Scanner\TriggerScanMessage;
use App\Repository\ScanResultRepository;
use App\Service\PdfGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/scanner')]
class ScannerController extends AbstractController
{
    public function __construct(
        private SiteVerificationService $verificationService,
        private RateLimiterFactory $scannerVerificationLimiter,
        private MessageBusInterface $messageBus,
        private ScanResultRepository $scanResultRepository,
        private PdfGenerator $pdfGenerator
    ) {
    }

    #[Route('/submit', name: 'api_scanner_submit', methods: ['POST'])]
    public function submit(Request $request, SessionInterface $session): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $url = $data['url'] ?? null;

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return new JsonResponse(['error' => 'URL invalide. Veuillez entrer une URL complète (https://...).'], 400);
        }

        $scanId = uniqid('scan_', true);
        $token = 'jja-lab-' . bin2hex(random_bytes(8));
        
        $session->set('scan_start_time_'.$scanId, time());
        $session->set('scan_url_'.$scanId, $url);
        $session->set('scan_token_'.$scanId, $token);
        $session->set('scan_verified_'.$scanId, false);

        return new JsonResponse([
            'scan_id' => $scanId,
            'token' => $token,
            'message' => 'Scan initialisé. Veuillez vérifier la propriété du site.',
        ]);
    }

    #[Route('/verify/{scanId}', name: 'api_scanner_verify', methods: ['POST'])]
    public function verify(string $scanId, Request $request, SessionInterface $session): JsonResponse
    {
        $limiter = $this->scannerVerificationLimiter->create($request->getClientIp());
        if (false === $limiter->consume(1)->isAccepted()) {
            return new JsonResponse(['error' => 'Trop de tentatives. Veuillez réessayer plus tard.'], 429);
        }

        $url = $session->get('scan_url_'.$scanId);
        $token = $session->get('scan_token_'.$scanId);

        if (!$url || !$token) {
            return new JsonResponse(['error' => 'Session de scan expirée ou introuvable.'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $method = $data['method'] ?? 'file';

        $isVerified = false;
        if ('dns' === $method) {
            $isVerified = $this->verificationService->verifyDns($url, $token);
        } else {
            $isVerified = $this->verificationService->verifyFile($url, $token);
        }

        if ($isVerified) {
            $session->set('scan_verified_'.$scanId, true);
            // Dispatch async scan
            $this->messageBus->dispatch(new TriggerScanMessage($scanId, $url));
            
            // Reset start time to begin "real" progress simulation
            $session->set('scan_start_time_'.$scanId, time());
            return new JsonResponse(['message' => 'Propriété vérifiée avec succès. Scan en cours...']);
        }

        return new JsonResponse(['error' => 'Vérification échouée. Assurez-vous que le token est correctement mis en place.'], 400);
    }

    #[Route('/status/{scanId}', name: 'api_scanner_status', methods: ['GET'])]
    public function status(string $scanId, SessionInterface $session): JsonResponse
    {
        $startTime = $session->get('scan_start_time_'.$scanId);
        $isVerified = $session->get('scan_verified_'.$scanId, false);
        
        if (!$startTime) {
            return new JsonResponse(['error' => 'Scan non trouvé.'], 404);
        }

        if (!$isVerified) {
            return new JsonResponse([
                'scan_id' => $scanId,
                'status' => 'awaiting_verification',
                'percentage' => 0,
                'current_step' => 'En attente de vérification de propriété...'
            ]);
        }

        $elapsed = time() - $startTime;
        $percentage = min(100, $elapsed * 10);

        $steps = [
            ['at' => 0, 'label' => 'Vérification réussie. Lancement du moteur...'],
            ['at' => 10, 'label' => 'Analyse DNS et résolution IP...'],
            ['at' => 30, 'label' => 'Scan des ports critiques...'],
            ['at' => 50, 'label' => 'Vérification des en-têtes de sécurité...'],
            ['at' => 70, 'label' => 'Recherche de vulnérabilités (Nuclei)...'],
            ['at' => 90, 'label' => 'Génération du rapport final...'],
            ['at' => 100, 'label' => 'Scan terminé.'],
        ];

        $currentStep = 'Initialisation...';
        foreach ($steps as $step) {
            if ($percentage >= $step['at']) {
                $currentStep = $step['label'];
            }
        }

        return new JsonResponse([
            'scan_id' => $scanId,
            'percentage' => $percentage,
            'current_step' => $currentStep,
            'status' => ($percentage < 100) ? 'scanning' : 'completed',
        ]);
    }

    #[Route('/download/{scanId}', name: 'api_scanner_download', methods: ['GET'])]
    public function download(string $scanId): Response
    {
        $scanResult = $this->scanResultRepository->findOneBy(['scanId' => $scanId]);

        if (!$scanResult || $scanResult->getStatus() !== 'completed') {
            // For mock/demo, if not in DB, we try to get from session or return error
            // But here we rely on the DB.
            return new Response('Rapport non disponible.', 404);
        }

        $pdfContent = $this->pdfGenerator->generate('scanner/report_pdf.html.twig', [
            'url' => $scanResult->getUrl(),
            'scan_id' => $scanResult->getScanId(),
            'results' => $scanResult->getRawOutput()
        ]);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="rapport-jja-lab-'.$scanId.'.pdf"'
        ]);
    }
}