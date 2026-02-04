<?php

namespace App\MessageHandler\Scanner;

use App\Entity\ScanResult;
use App\Message\Scanner\TriggerScanMessage;
use App\Repository\ScanResultRepository;
use App\Service\NucleiScanner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TriggerScanHandler
{
    public function __construct(
        private NucleiScanner $nucleiScanner,
        private EntityManagerInterface $entityManager,
        private ScanResultRepository $scanResultRepository
    ) {
    }

    public function __invoke(TriggerScanMessage $message): void
    {
        $scanResult = $this->scanResultRepository->findOneBy(['scanId' => $message->getScanId()]);

        if (!$scanResult) {
            $scanResult = new ScanResult();
            $scanResult->setScanId($message->getScanId());
            $scanResult->setUrl($message->getUrl());
            $this->entityManager->persist($scanResult);
        }

        $scanResult->setStatus('processing');
        $scanResult->setErrorMessage(null); // Reset previous errors if any
        $this->entityManager->flush();

        $startTime = microtime(true);
        try {
            $results = $this->nucleiScanner->scan($message->getUrl());
            $scanResult->setRawOutput($results);
            $scanResult->setStatus('completed');
        } catch (\Exception $e) {
            $scanResult->setStatus('failed');
            $scanResult->setErrorMessage($e->getMessage());
        } finally {
            $duration = (int) (microtime(true) - $startTime);
            $scanResult->setDuration($duration);
            $this->entityManager->flush();
            
            // Clean up memory
            $this->entityManager->clear();
        }
    }
}
