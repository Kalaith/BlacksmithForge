<?php

namespace App\Services;

use App\Repositories\UpgradeRepository;
use Psr\Log\LoggerInterface;

class UpgradeService
{
    private UpgradeRepository $repository;
    private LoggerInterface $logger;

    public function __construct(UpgradeRepository $repository, LoggerInterface $logger)
    {
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function getAllUpgrades(): array
    {
        try {
            return $this->repository->findAll();
        } catch (\Exception $e) {
            $this->logger->error('Failed to get all upgrades: ' . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve upgrades');
        }
    }

    // Add other upgrade methods as needed
}
