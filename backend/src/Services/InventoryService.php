<?php

namespace App\Services;

use App\Repositories\InventoryRepository;
use App\Repositories\MaterialRepository;
use Psr\Log\LoggerInterface;

class InventoryService
{
    private InventoryRepository $repository;
    private MaterialRepository $materialRepository;
    private LoggerInterface $logger;

    public function __construct(InventoryRepository $repository, MaterialRepository $materialRepository, LoggerInterface $logger)
    {
        $this->repository = $repository;
        $this->materialRepository = $materialRepository;
        $this->logger = $logger;
    }

    public function getUserInventory(int $userId): array
    {
        try {
            return $this->repository->findBy(['user_id' => $userId]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get inventory for user {$userId}: " . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve inventory');
        }
    }

    // Add other inventory methods as needed
}
