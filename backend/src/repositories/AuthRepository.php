<?php

namespace App\Repositories;

use PDO;
use App\Models\User;

class AuthRepository extends BaseRepository
{
    protected $table = 'users';
    protected $fillable = [
        'username',
        'password',
    ];

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Create a new user
     */
    public function createUser(User $user): int
    {
        $data = [
            'username' => $user->username,
            'password' => $user->password,
        ];

        return $this->create($data);
    }

    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?User
    {
        $result = $this->findOneBy(['username' => $username]);
        return $result ? $this->mapToModel($result) : null;
    }

    /**
     * Find user by ID and return model
     */
    public function findUser(int $id): ?User
    {
        $result = $this->find($id);
        return $result ? $this->mapToModel($result) : null;
    }

    /**
     * Check if username exists
     */
    public function usernameExists(string $username): bool
    {
        return $this->exists(['username' => $username]);
    }

    /**
     * Map database result to User model
     */
    private function mapToModel(array $data): User
    {
        return User::fromArray($data);
    }
}
