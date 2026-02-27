<?php

namespace App\Repository;

use App\Enum\LeadStatus;
use App\Entity\Lead;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lead>
 */
class LeadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lead::class);
    }

    public function countTotal(\DateTimeInterface $start, \DateTimeInterface $end): int
    {
        return $this->createQueryBuilder('l')
            ->select('count(l.id)')
            ->andWhere('l.createdAt >= :start')
            ->andWhere('l.createdAt <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countConverted(\DateTimeInterface $start, \DateTimeInterface $end): int
    {
        return $this->createQueryBuilder('l')
            ->select('count(l.id)')
            ->andWhere('l.status = :status')
            ->andWhere('l.createdAt >= :start')
            ->andWhere('l.createdAt <= :end')
            ->setParameter('status', LeadStatus::WON)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return list<array{date: string, count: int}> */
    public function countByDay(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $leads = $this->createQueryBuilder('l')
            ->select('l.createdAt')
            ->andWhere('l.createdAt >= :start')
            ->andWhere('l.createdAt <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('l.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($leads as $lead) {
            $date = $lead['createdAt']->format('Y-m-d');
            $counts[$date] = ($counts[$date] ?? 0) + 1;
        }

        $formatted = [];
        foreach ($counts as $date => $count) {
            $formatted[] = ['date' => $date, 'count' => $count];
        }

        return $formatted;
    }

    /**
     * Finds all leads sorted by a specific field.
     * Fetches appointments collection eagerly to avoid N+1 in templates? 
     * Actually, if we just need the count, we can do a LEFT JOIN and scalar result,
     * BUT we need the Lead entity.
     * 
     * Best approach for Kanban: fetch all data needed.
     */
    /** @return list<Lead> */
    public function findAllWithAppointmentsCount(string $sort = 'createdAt', string $direction = 'DESC'): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.appointments', 'a')
            ->addSelect('a'); // Eager load appointments to avoid N+1 on count check

        // Basic whitelist for sorting
        $allowedSorts = ['createdAt', 'name', 'status'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'createdAt';
        }
        
        $qb->orderBy('l.' . $sort, $direction);

        return $qb->getQuery()->getResult();
    }
}