<?php

namespace App\Services;

use App\Repositories\UpgradeRepository;
use App\Repositories\BlacksmithProfileRepository;
use Psr\Log\LoggerInterface;

class UpgradeService
{
    private UpgradeRepository $repository;
    private BlacksmithProfileRepository $profileRepository;
    private LoggerInterface $logger;

    public function __construct(
        UpgradeRepository $repository,
        BlacksmithProfileRepository $profileRepository,
        LoggerInterface $logger
    )
    {
        $this->repository = $repository;
        $this->profileRepository = $profileRepository;
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

    public function purchaseUpgrade(int $userId, int $upgradeId): array
    {
        if ($userId <= 0) {
            return [
                'success' => false,
                'message' => 'User is not authenticated'
            ];
        }
        if ($upgradeId <= 0) {
            return [
                'success' => false,
                'message' => 'Upgrade ID is required'
            ];
        }

        try {
            $upgrade = $this->repository->findUpgradeById($upgradeId);
            if (!$upgrade) {
                return [
                    'success' => false,
                    'message' => 'Upgrade not found'
                ];
            }

            $profile = $this->getOrCreateProfile($userId);
            $unlockLevel = (int) ($upgrade['unlock_level'] ?? 1);
            if ($profile->level < $unlockLevel) {
                return [
                    'success' => false,
                    'message' => 'Upgrade is locked',
                    'required_level' => $unlockLevel,
                    'current_level' => $profile->level
                ];
            }

            if ($this->repository->userHasUpgrade($userId, $upgradeId)) {
                return [
                    'success' => false,
                    'message' => 'Upgrade already purchased'
                ];
            }

            $cost = (int) ($upgrade['cost'] ?? 0);
            if ($profile->coins < $cost) {
                return [
                    'success' => false,
                    'message' => 'Insufficient gold',
                    'required' => $cost,
                    'available' => $profile->coins
                ];
            }

            $this->repository->beginTransaction();
            try {
                $this->repository->addUserUpgrade($userId, $upgradeId);
                $newCoins = $profile->coins - $cost;
                $this->profileRepository->updateByUserId($userId, [
                    'coins' => $newCoins
                ]);
                $this->repository->commit();
            } catch (\Exception $e) {
                $this->repository->rollback();
                throw $e;
            }

            $this->logger->info("User {$userId} purchased upgrade {$upgradeId} for {$cost} gold");

            return [
                'success' => true,
                'upgrade' => $upgrade,
                'cost' => $cost,
                'new_gold' => $profile->coins - $cost
            ];
        } catch (\Exception $e) {
            $this->logger->error("Failed to purchase upgrade for user {$userId}: " . $e->getMessage());
            throw new \RuntimeException('Failed to purchase upgrade');
        }
    }

    private function getOrCreateProfile(int $userId)
    {
        $profile = $this->profileRepository->findByUserId($userId);
        if (!$profile) {
            $profile = $this->profileRepository->createDefaultProfile($userId, 'New Forge');
        }
        return $profile;
    }
}
