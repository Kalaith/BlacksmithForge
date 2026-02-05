<?php

namespace App\Repositories;

use PDO;
use App\Models\BlacksmithProfile;

class BlacksmithProfileRepository extends BaseRepository
{
    protected $table = 'blacksmith_profiles';
    protected $fillable = [
        'user_id',
        'forge_name',
        'level',
        'reputation',
        'coins',
        'crafting_mastery',
        'settings',
        'last_seen_at',
    ];

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    public function findByUserId(int $userId): ?BlacksmithProfile
    {
        $result = $this->findOneBy(['user_id' => $userId]);
        if (!$result) {
            return null;
        }

        $result['crafting_mastery'] = $this->decodeJson($result['crafting_mastery'] ?? null);
        $result['settings'] = $this->decodeJson($result['settings'] ?? null);

        return new BlacksmithProfile($result);
    }

    public function createDefaultProfile(int $userId, string $forgeName): BlacksmithProfile
    {
        $data = [
            'user_id' => $userId,
            'forge_name' => $forgeName,
            'level' => 1,
            'reputation' => 0,
            'coins' => 100,
            'crafting_mastery' => $this->encodeJson([]),
            'settings' => $this->encodeJson([]),
            'last_seen_at' => date('Y-m-d H:i:s'),
        ];

        $id = $this->create($data);
        return new BlacksmithProfile(array_merge($data, ['id' => $id]));
    }

    public function updateLastSeen(int $userId): void
    {
        $this->updateByUserId($userId, [
            'last_seen_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateByUserId(int $userId, array $data): bool
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
}
