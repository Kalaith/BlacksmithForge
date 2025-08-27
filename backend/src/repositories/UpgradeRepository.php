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

    // Add upgrade-specific methods as needed
}
