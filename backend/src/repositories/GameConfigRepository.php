<?php

namespace App\Repositories;

use PDO;

class GameConfigRepository extends BaseRepository
{
    protected $table = 'game_config';
    protected $fillable = [
        'key',
        'value'
    ];

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    public function getValue(string $key): mixed
    {
        $row = $this->findOneBy(['key' => $key]);
        if (!$row) {
            return null;
        }

        $decoded = $this->decodeJson($row['value'] ?? null);
        return $decoded;
    }
}
