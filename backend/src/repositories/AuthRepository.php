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
        'email',
        'auth_provider',
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
            'email' => $user->email ?? null,
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
     * Find user by ID and return array
     */
    public function findById(int $id): ?array
    {
        return $this->find($id);
    }

    /**
     * Upsert a WebHatchery user based on JWT claims
     */
    public function upsertWebHatcheryUser(int $id, ?string $email, string $username): array
    {
        $sql = "INSERT INTO {$this->table} (id, email, username, password, auth_provider)
                VALUES (:id, :email, :username, :password, :auth_provider)
                ON DUPLICATE KEY UPDATE
                    email = VALUES(email),
                    username = VALUES(username),
                    auth_provider = VALUES(auth_provider),
                    updated_at = CURRENT_TIMESTAMP";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':email' => $email ?? '',
            ':username' => $username,
            ':password' => null,
            ':auth_provider' => 'webhatchery',
        ]);

        $user = $this->find($id);
        return $user ?: [
            'id' => $id,
            'email' => $email ?? '',
            'username' => $username,
            'auth_provider' => 'webhatchery',
        ];
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
