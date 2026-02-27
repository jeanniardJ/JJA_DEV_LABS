<?php

namespace App\Repository;

use App\Entity\LabStation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LabStation>
 */
class LabStationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LabStation::class);
    }

    /**
     * @return LabStation[]
     */
    public function findAllSorted(): array
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.position', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
