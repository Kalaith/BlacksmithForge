<?php
namespace App\Actions;

use App\Services\UpgradeService;
use App\Utils\ContainerConfig;

class UpgradeActions {
    public static function getAll() {
        try {
            $upgradeService = ContainerConfig::createContainer()->get(UpgradeService::class);
            $upgrades = $upgradeService->getAllUpgrades();
            return [
                'success' => true,
                'data' => $upgrades,
                'count' => count($upgrades)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function purchase($userId, $upgradeId) {
        return [
            'success' => false,
            'message' => 'Upgrade purchase not implemented'
        ];
    }
}
