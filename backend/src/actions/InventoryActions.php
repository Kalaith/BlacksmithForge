<?php
namespace App\Actions;

class InventoryActions {
    // Get inventory for a user
    public static function getInventory($userId) {
        // Example: return a list of Material models as inventory
        return [
            new \App\Models\Material('Iron Ore', 5, 'common', 'Basic material for most weapons', '⛏️'),
            new \App\Models\Material('Coal', 2, 'common', 'Fuel for the forge', '🔥'),
        ];
    }

    public static function addItem($userId, $itemId, $quantity) {
        $material = new \App\Models\Material('Iron Ore', 5, 'common', 'Basic material for most weapons', '⛏️');
        return ["added" => true, "item" => $material, "quantity" => $quantity];
    }

    public static function removeItem($userId, $itemId, $quantity) {
        $material = new \App\Models\Material('Iron Ore', 5, 'common', 'Basic material for most weapons', '⛏️');
        return ["removed" => true, "item" => $material, "quantity" => $quantity];
    }
}
