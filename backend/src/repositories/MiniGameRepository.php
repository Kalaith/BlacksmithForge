<?php

namespace App\Repositories;

use PDO;

class MiniGameRepository extends BaseRepository
{
    protected $table = 'minigame_history';
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

    // Add minigame-specific methods as needed
}
