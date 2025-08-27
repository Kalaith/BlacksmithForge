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

    // Add customer-specific methods as needed
}
