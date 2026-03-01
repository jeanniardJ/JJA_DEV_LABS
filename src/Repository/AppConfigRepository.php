<?php

namespace App\Repository;

use App\Entity\AppConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppConfig>
 */
class AppConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppConfig::class);
    }

    public function getValue(string $key, ?string $default = null): ?string
    {
        $config = $this->findOneBy(['settingKey' => $key]);
        return $config ? $config->getSettingValue() : $default;
    }
}
