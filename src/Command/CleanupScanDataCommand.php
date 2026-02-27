<?php

namespace App\Command;

use App\Repository\ScanResultRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cleanup-scan-data',
    description: 'Supprime les résultats de scan vieux de plus de 24h (RGPD)',
)]
class CleanupScanDataCommand extends Command
{
    public function __construct(
        private ScanResultRepository $scanResultRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $limitDate = new \DateTimeImmutable('-24 hours');
        
        $count = $this->scanResultRepository->createQueryBuilder('s')
            ->delete()
            ->where('s.createdAt < :limit')
            ->setParameter('limit', $limitDate)
            ->getQuery()
            ->execute();

        $io->success(sprintf('Suppression de %d rapports obsolètes terminée.', $count));

        return Command::SUCCESS;
    }
}
