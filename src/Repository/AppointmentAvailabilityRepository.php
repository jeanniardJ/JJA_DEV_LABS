<?php

namespace App\Repository;

use App\Entity\AppointmentAvailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppointmentAvailability>
 */
class AppointmentAvailabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppointmentAvailability::class);
    }

    /**
     * @return AppointmentAvailability[]
     */
    public function findByDayOfWeek(int $dayOfWeek): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.dayOfWeek = :val')
            ->setParameter('val', $dayOfWeek)
            ->orderBy('a.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
