<?php

namespace App\Repositories;

use PDO;

/**
 * InventoryRepository provides database operations for inventory management
 * Uses a JSON payload stored in the inventory.items column.
 */
class InventoryRepository extends BaseRepository
{
    protected $table = 'inventory';
    protected $fillable = [
        'user_id',
        'items',
    ];

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Get all crafted items for a user.
     */
    public function getUserInventory(int $userId): array
    {
        $record = $this->getOrCreateRecord($userId);
        return $record['items'] ?? [];
    }

    /**
     * Get material quantities for a user.
     */
    public function getUserMaterials(int $userId): array
    {
        $record = $this->getOrCreateRecord($userId);
        return $record['materials'] ?? [];
    }

    /**
     * Add a crafted item to the user's inventory.
     */
    public function addItem(int $userId, array $item): string
    {
        $record = $this->getOrCreateRecord($userId);
        $items = $record['items'] ?? [];

        $itemId = $item['id'] ?? $this->generateItemId();
        $item['id'] = $itemId;

        $items[] = $item;

        $record['items'] = $items;
        $this->saveRecord($userId, $record);

        return (string) $itemId;
    }

    /**
     * Remove an item by ID from the user's inventory.
     */
    public function removeItem(int $userId, $itemId): bool
    {
        $record = $this->getOrCreateRecord($userId);
        $items = $record['items'] ?? [];
        $initialCount = count($items);

        $items = array_values(array_filter($items, function ($item) use ($itemId) {
            return (string) ($item['id'] ?? '') !== (string) $itemId;
        }));

        if (count($items) === $initialCount) {
            return false;
        }

        $record['items'] = $items;
        $this->saveRecord($userId, $record);

        return true;
    }

    /**
     * Get a specific item by ID for a user.
     */
    public function getItemById(int $userId, $itemId): ?array
    {
        $record = $this->getOrCreateRecord($userId);
        $items = $record['items'] ?? [];

        foreach ($items as $item) {
            if ((string) ($item['id'] ?? '') === (string) $itemId) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Add materials to a user.
     */
    public function addMaterialToUser(int $userId, string $materialName, int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $record = $this->getOrCreateRecord($userId);
        $materials = $record['materials'] ?? [];
        $materials[$materialName] = ($materials[$materialName] ?? 0) + $quantity;

        $record['materials'] = $materials;
        $this->saveRecord($userId, $record);
        return true;
    }

    /**
     * Reserve materials for crafting (move from materials to reserved).
     */
    public function reserveMaterial(int $userId, string $materialName, int $quantity): void
    {
        $record = $this->getOrCreateRecord($userId);
        $materials = $record['materials'] ?? [];
        $reserved = $record['reserved_materials'] ?? [];

        $available = $materials[$materialName] ?? 0;
        if ($available < $quantity) {
            throw new \RuntimeException("Insufficient {$materialName} to reserve");
        }

        $materials[$materialName] = $available - $quantity;
        $reserved[$materialName] = ($reserved[$materialName] ?? 0) + $quantity;

        $record['materials'] = $materials;
        $record['reserved_materials'] = $reserved;
        $this->saveRecord($userId, $record);
    }

    /**
     * Consume reserved materials (remove from reserved).
     */
    public function consumeMaterial(int $userId, string $materialName, int $quantity): void
    {
        $record = $this->getOrCreateRecord($userId);
        $reserved = $record['reserved_materials'] ?? [];

        $available = $reserved[$materialName] ?? 0;
        if ($available < $quantity) {
            throw new \RuntimeException("Insufficient reserved {$materialName} to consume");
        }

        $reserved[$materialName] = $available - $quantity;
        if ($reserved[$materialName] <= 0) {
            unset($reserved[$materialName]);
        }

        $record['reserved_materials'] = $reserved;
        $this->saveRecord($userId, $record);
    }

    /**
     * Consume materials directly (no reservation).
     */
    public function consumeMaterials(int $userId, array $materialsToConsume): bool
    {
        $record = $this->getOrCreateRecord($userId);
        $materials = $record['materials'] ?? [];

        foreach ($materialsToConsume as $materialName => $quantity) {
            $available = $materials[$materialName] ?? 0;
            if ($available < $quantity) {
                return false;
            }
        }

        foreach ($materialsToConsume as $materialName => $quantity) {
            $materials[$materialName] = ($materials[$materialName] ?? 0) - $quantity;
            if ($materials[$materialName] <= 0) {
                unset($materials[$materialName]);
            }
        }

        $record['materials'] = $materials;
        $this->saveRecord($userId, $record);
        return true;
    }

    private function getOrCreateRecord(int $userId): array
    {
        $row = $this->findOneBy(['user_id' => $userId]);
        if (!$row) {
            $record = [
                'items' => [],
                'materials' => [],
                'reserved_materials' => []
            ];
            $this->create([
                'user_id' => $userId,
                'items' => $this->encodeJson($record),
            ]);
            return $record;
        }

        $decoded = $this->decodeJson($row['items'] ?? null);
        return $this->normalizeRecord($decoded);
    }

    private function saveRecord(int $userId, array $record): void
    {
        $this->updateByUserId($userId, [
            'items' => $this->encodeJson($record)
        ]);
    }

    private function updateByUserId(int $userId, array $data): bool
    {
        $data = $this->filterFillable($data);
        if (empty($data)) {
            return false;
        }

        $setParts = [];
        foreach (array_keys($data) as $field) {
            $setParts[] = "{$field} = ?";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE user_id = ?";
        $params = array_values($data);
        $params[] = $userId;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    private function normalizeRecord($decoded): array
    {
        if (is_array($decoded) && array_key_exists('items', $decoded)) {
            return array_merge([
                'items' => [],
                'materials' => [],
                'reserved_materials' => []
            ], $decoded);
        }

        if (is_array($decoded)) {
            return [
                'items' => array_values($decoded),
                'materials' => [],
                'reserved_materials' => []
            ];
        }

        return [
            'items' => [],
            'materials' => [],
            'reserved_materials' => []
        ];
    }

    private function generateItemId(): string
    {
        return 'item_' . bin2hex(random_bytes(6));
    }
}
