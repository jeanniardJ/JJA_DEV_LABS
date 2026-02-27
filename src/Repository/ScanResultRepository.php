<?php

namespace App\Repository;

use App\Entity\ScanResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScanResult>
 */
class ScanResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScanResult::class);
    }

    public function countTotal(\DateTimeInterface $start, \DateTimeInterface $end): int
    {
        return $this->createQueryBuilder('s')
            ->select('count(s.id)')
            ->andWhere('s.createdAt >= :start')
            ->andWhere('s.createdAt <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return list<array{severity: string, count: int}> */
    public function getSeverityStats(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $results = $this->createQueryBuilder('s')
            ->andWhere('s.createdAt >= :start')
            ->andWhere('s.createdAt <= :end')
            ->andWhere('s.status = :status')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getResult();

        $stats = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
            'info' => 0,
        ];

        foreach ($results as $scan) {
            $maxSeverity = $scan->getMaxSeverity();
            if (isset($stats[$maxSeverity])) {
                $stats[$maxSeverity]++;
            }
        }

        $formattedStats = [];
        foreach ($stats as $severity => $count) {
            if ($count > 0) {
                $formattedStats[] = ['severity' => $severity, 'count' => $count];
            }
        }

        return $formattedStats;
    }
}
