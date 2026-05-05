<?php

declare(strict_types=1);

namespace App\Actions;

use App\Services\UpgradeService;

class UpgradeActions
{
    public function __construct(private readonly UpgradeService $upgradeService)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getAll(): array
    {
        try {
            $upgrades = $this->upgradeService->getAllUpgrades();
            return [
                'success' => true,
                'data' => $upgrades,
                'count' => count($upgrades),
            ];
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function purchase(int $userId, int $upgradeId): array
    {
        try {
            return $this->upgradeService->purchaseUpgrade($userId, $upgradeId);
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getPurchased(int $userId): array
    {
        try {
            $upgradeIds = $this->upgradeService->getUserUpgradeIds($userId);
            return [
                'success' => true,
                'data' => $upgradeIds,
                'count' => count($upgradeIds),
            ];
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function failure(\Exception $e): array
    {
        return [
            'success' => false,
            'message' => $e->getMessage(),
        ];
    }
}
