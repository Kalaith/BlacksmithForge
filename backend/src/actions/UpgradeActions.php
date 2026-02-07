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
        try {
            $upgradeService = ContainerConfig::createContainer()->get(UpgradeService::class);
            $result = $upgradeService->purchaseUpgrade((int) $userId, (int) $upgradeId);
            return $result;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public static function getPurchased($userId) {
        try {
            $upgradeService = ContainerConfig::createContainer()->get(UpgradeService::class);
            $upgradeIds = $upgradeService->getUserUpgradeIds((int) $userId);
            return [
                'success' => true,
                'data' => $upgradeIds,
                'count' => count($upgradeIds)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
