<?php

declare(strict_types=1);

namespace App\Database;

use App\External\DatabaseService;

class Seeder
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = DatabaseService::getInstance()->getPdo();
    }

    public function seed(): void
    {
        $this->initDatabaseSchema();
        $this->seedMaterials();
        $this->seedRecipes();
        $this->seedCustomers();
        $this->seedForgeUpgrades();
        echo "Database seeded successfully!\n";
    }

    private function initDatabaseSchema(): void
    {
        $sqlFile = dirname(__DIR__, 2) . '/database/init_db.sql';
        if (!file_exists($sqlFile)) {
            throw new \RuntimeException("init_db.sql not found at $sqlFile");
        }
        $sql = file_get_contents($sqlFile);
        // Split SQL statements by semicolon, ignoring those inside strings/comments
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $stmt) {
            if ($stmt !== '') {
                $this->pdo->exec($stmt);
            }
        }
    }

    private function seedMaterials(): void
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM materials");
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            return;
        }
        $materials = [
            ['Iron Ore', 5, 'common', 'Basic material for most weapons', '⛏️'],
            ['Coal', 2, 'common', 'Fuel for the forge', '🔥'],
            ['Wood', 3, 'common', 'For handles and hilts', '🌳'],
            ['Leather', 4, 'common', 'For grips and armor padding', '🐄'],
            ['Silver Ore', 25, 'rare', 'Precious metal for decorative elements', '✨'],
            ['Mythril', 100, 'legendary', 'Rare magical metal', '💎']
        ];
        $stmt = $this->pdo->prepare("INSERT INTO materials (name, cost, quality, description, icon) VALUES (?, ?, ?, ?, ?)");
        foreach ($materials as $mat) {
            $stmt->execute($mat);
        }
    }

    private function seedRecipes(): void
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM recipes");
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            return;
        }
        $recipes = [
            ['Iron Dagger', json_encode(['Iron Ore' => 1, 'Wood' => 1, 'Coal' => 1]), 15, 1, '🗡️'],
            ['Iron Sword', json_encode(['Iron Ore' => 2, 'Wood' => 1, 'Leather' => 1, 'Coal' => 2]), 35, 2, '⚔️'],
            ['Steel Axe', json_encode(['Iron Ore' => 3, 'Wood' => 2, 'Coal' => 3]), 45, 3, '🪓'],
            ['Silver Blade', json_encode(['Silver Ore' => 1, 'Iron Ore' => 1, 'Wood' => 1, 'Coal' => 2]), 80, 4, '🌟']
        ];
        $stmt = $this->pdo->prepare("INSERT INTO recipes (name, materials, sellPrice, difficulty, icon) VALUES (?, ?, ?, ?, ?)");
        foreach ($recipes as $rec) {
            $stmt->execute($rec);
        }
    }

    private function seedCustomers(): void
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM customers");
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            return;
        }
        $customers = [
            ['Village Guard', 50, 'durability', 0, '🛡️'],
            ['Traveling Merchant', 30, 'value', 0, '🎒'],
            ['Noble Knight', 150, 'quality', 0, '👑'],
            ['Young Adventurer', 25, 'balanced', 0, '🗡️']
        ];
        $stmt = $this->pdo->prepare("INSERT INTO customers (name, budget, preferences, reputation, icon) VALUES (?, ?, ?, ?, ?)");
        foreach ($customers as $cust) {
            $stmt->execute($cust);
        }
    }

    private function seedForgeUpgrades(): void
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM upgrades");
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            return;
        }
        $upgrades = [
            ['Better Bellows', 100, 'Reduces coal consumption', '💨'],
            ['Precision Anvil', 200, 'Improves crafting accuracy', '🔨'],
            ['Master Tools', 500, 'Unlocks advanced recipes', '⚒️']
        ];
        $stmt = $this->pdo->prepare("INSERT INTO upgrades (name, cost, description, icon) VALUES (?, ?, ?, ?)");
        foreach ($upgrades as $upg) {
            $stmt->execute($upg);
        }
    }
}

if (php_sapi_name() === 'cli') {
    require_once __DIR__ . '/../../vendor/autoload.php';
    $seeder = new Seeder();
    $seeder->seed();
}
