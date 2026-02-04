<?php

namespace App\Repository;

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

    /**
     * Finds all leads sorted by a specific field.
     * Fetches appointments collection eagerly to avoid N+1 in templates? 
     * Actually, if we just need the count, we can do a LEFT JOIN and scalar result,
     * BUT we need the Lead entity.
     * 
     * Best approach for Kanban: fetch all data needed.
     */
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