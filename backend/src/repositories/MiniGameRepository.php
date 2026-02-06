<?php

namespace App\Repositories;

use PDO;

class MiniGameRepository extends BaseRepository
{
    protected $table = 'minigames';
    protected $fillable = [
        'user_id',
        'game_type',
        'score',
        'played_at',
        // Add other minigame fields as needed
    ];

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    public function logGame(array $data): int
    {
        return $this->create($data);
    }

    public function getUserHistory(int $userId, int $limit = 50): array
    {
        return $this->findBy(['user_id' => $userId], ['created_at' => 'DESC'], $limit);
    }
}
