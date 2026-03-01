<?php

namespace App\Service;

use App\Entity\SystemLog;
use Doctrine\ORM\EntityManagerInterface;

class SystemLogService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function log(string $message, string $level = 'INFO', string $category = 'GENERAL'): void
    {
        $log = new SystemLog();
        $log->setMessage($message);
        $log->setLevel(strtoupper($level));
        $log->setCategory(strtoupper($category));

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function info(string $message, string $category = 'GENERAL'): void
    {
        $this->log($message, 'INFO', $category);
    }

    public function success(string $message, string $category = 'GENERAL'): void
    {
        $this->log($message, 'SUCCESS', $category);
    }

    public function warning(string $message, string $category = 'GENERAL'): void
    {
        $this->log($message, 'WARNING', $category);
    }

    public function error(string $message, string $category = 'GENERAL'): void
    {
        $this->log($message, 'ERROR', $category);
    }

    public function system(string $message, string $category = 'SYSTEM'): void
    {
        $this->log($message, 'SYSTEM', $category);
    }
}
