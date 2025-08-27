<?php
namespace App\Actions;

class UpgradeActions {
    public static function getAll() {
        return [
            new \App\Models\ForgeUpgrade('Better Bellows', 100, 'Reduces coal consumption', '💨'),
            new \App\Models\ForgeUpgrade('Precision Anvil', 200, 'Improves crafting accuracy', '🔨'),
        ];
    }
    public static function purchase($userId, $upgradeId) {
        $upgrade = new \App\Models\ForgeUpgrade('Better Bellows', 100, 'Reduces coal consumption', '💨');
        return ["purchased" => true, "upgrade" => $upgrade];
    }
}
