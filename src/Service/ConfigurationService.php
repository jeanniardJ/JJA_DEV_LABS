<?php

namespace App\Service;

use App\Repository\AppConfigRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\AppConfig;

class ConfigurationService
{
    public function __construct(
        private AppConfigRepository $repository,
        private EntityManagerInterface $entityManager
    ) {}

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->repository->getValue($key, $default);
    }

    public function set(string $key, ?string $value, ?string $description = null): void
    {
        $config = $this->repository->findOneBy(['settingKey' => $key]);
        
        if (!$config) {
            $config = new AppConfig();
            $config->setSettingKey($key);
        }
        
        $config->setSettingValue($value);
        if ($description) {
            $config->setDescription($description);
        }

        $this->entityManager->persist($config);
        $this->entityManager->flush();
    }
}
