<?php

namespace App\Repositories;

use PDO;

class CraftingRepository extends BaseRepository
{
    protected $table = 'crafting_history';
    protected $fillable = [
        'user_id',
        'recipe_id',
        'materials_used',
        'crafted_at',
        // Add other crafting fields as needed
    ];

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    // Add crafting-specific methods as needed
}
