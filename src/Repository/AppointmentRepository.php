<?php

namespace App\Repository;

use App\Entity\Appointment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Appointment>
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    /**
     * @return Appointment[]
     */
    public function findByDay(\DateTimeImmutable $day): array
    {
        $start = $day->setTime(0, 0, 0);
        $end = $day->setTime(23, 59, 59);

        return $this->createQueryBuilder('a')
            ->andWhere('a.startsAt >= :start')
            ->andWhere('a.startsAt <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }
}
