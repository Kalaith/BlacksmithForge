<?php

namespace App\Repositories;

use PDO;

class CustomerRepository extends BaseRepository
{
    protected $table = 'customers';
    protected $fillable = [
        'name',
        'budget',
        'preferences',
        'reputation',
        'icon',
        // Add other customer fields as needed
    ];

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    public function getAllCustomerTypes(): array
    {
        $stmt = $this->query("SELECT * FROM customer_types ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCustomerTypeById(int $typeId): ?array
    {
        $stmt = $this->query("SELECT * FROM customer_types WHERE id = ?", [$typeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getCurrentCustomerForUser(int $userId): ?array
    {
        $sql = "SELECT ci.*, ct.name as type_name, ct.preferences, ct.icon, ct.budget_min, ct.budget_max, ct.description
                FROM customer_instances ci
                JOIN customer_types ct ON ct.id = ci.type_id
                WHERE ci.user_id = ? AND ci.status = 'waiting'
                ORDER BY ci.created_at DESC
                LIMIT 1";
        $stmt = $this->query($sql, [$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createCustomerForUser(array $data): int
    {
        $sql = "INSERT INTO customer_instances (user_id, type_id, name, budget, preferences, icon, description, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->query($sql, [
            $data['user_id'],
            $data['type_id'],
            $data['name'],
            $data['budget'],
            $data['preferences'],
            $data['icon'],
            $data['description'],
            $data['status'] ?? 'waiting',
            $data['created_at'] ?? date('Y-m-d H:i:s'),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function removeCustomerForUser(int $userId, int $customerId): void
    {
        $sql = "UPDATE customer_instances SET status = 'completed', updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND user_id = ?";
        $this->query($sql, [$customerId, $userId]);
    }

    public function removeCurrentCustomerForUser(int $userId): void
    {
        $sql = "UPDATE customer_instances SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP
                WHERE user_id = ? AND status = 'waiting'";
        $this->query($sql, [$userId]);
    }

    public function getCustomerById(int $customerId): ?array
    {
        $sql = "SELECT * FROM customer_instances WHERE id = ? LIMIT 1";
        $stmt = $this->query($sql, [$customerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function logTransaction(array $data): void
    {
        $sql = "INSERT INTO customer_sales (user_id, customer_id, item_id, sale_price, base_price, modifier, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $this->query($sql, [
            $data['user_id'],
            $data['customer_id'],
            $data['item_id'],
            $data['sale_price'],
            $data['base_price'],
            $data['modifier'],
            $data['created_at'] ?? date('Y-m-d H:i:s'),
        ]);
    }
}
