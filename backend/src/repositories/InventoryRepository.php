<?php

namespace App\Repositories;

use PDO;

/**
 * InventoryRepository provides database operations for inventory management
 */
class InventoryRepository extends BaseRepository
{
    protected $table = 'inventory';
    protected $fillable = [
        'user_id',
        'item_id',
        'quantity',
        // Add other inventory fields as needed
    ];

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Get inventory for a user
     */
    public function getByUserId(int $userId): array
    {
        return $this->findBy(['user_id' => $userId]);
    }

    /**
     * Add item to inventory
     */
    public function addItem(int $userId, int $itemId, int $quantity): int
    {
        $existing = $this->findOneBy(['user_id' => $userId, 'item_id' => $itemId]);
        if ($existing) {
            $newQty = $existing['quantity'] + $quantity;
            $this->update($existing['id'], ['quantity' => $newQty]);
            return $existing['id'];
        } else {
            return $this->create([
                'user_id' => $userId,
                'item_id' => $itemId,
                'quantity' => $quantity
            ]);
        }
    }

    /**
     * Remove item from inventory
     */
    public function removeItem(int $userId, int $itemId, int $quantity): bool
    {
        $existing = $this->findOneBy(['user_id' => $userId, 'item_id' => $itemId]);
        if ($existing && $existing['quantity'] >= $quantity) {
            $newQty = $existing['quantity'] - $quantity;
            if ($newQty > 0) {
                return $this->update($existing['id'], ['quantity' => $newQty]);
            } else {
                return $this->delete($existing['id']);
            }
        }
        return false;
    }

    // Add more inventory-specific methods as needed
}
