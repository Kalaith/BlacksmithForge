<?php

namespace App\Repositories;

use PDO;

class UpgradeRepository extends BaseRepository
{
    protected $table = 'upgrades';
    protected $fillable = [
        'name',
        'cost',
        'description',
        'icon',
        // Add other upgrade fields as needed
    ];

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    public function findUpgradeById(int $upgradeId): ?array
    {
        return $this->find($upgradeId);
    }

    public function userHasUpgrade(int $userId, int $upgradeId): bool
    {
        $stmt = $this->query(
            "SELECT 1 FROM user_upgrades WHERE user_id = ? AND upgrade_id = ? LIMIT 1",
            [$userId, $upgradeId]
        );
        return (bool) $stmt->fetchColumn();
    }

    public function addUserUpgrade(int $userId, int $upgradeId): int
    {
        $this->query(
            "INSERT INTO user_upgrades (user_id, upgrade_id) VALUES (?, ?)",
            [$userId, $upgradeId]
        );
        return (int) $this->pdo->lastInsertId();
    }

    public function getUserUpgradeIds(int $userId): array
    {
        $stmt = $this->query(
            "SELECT upgrade_id FROM user_upgrades WHERE user_id = ? ORDER BY purchased_at DESC",
            [$userId]
        );
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }
}
