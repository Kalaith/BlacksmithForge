<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

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

    public function createGuestUser(string $username): array
    {
        $candidate = $username;
        $attempt = 0;

        while ($this->usernameExists($candidate)) {
            $attempt++;
            $candidate = $username . '_' . $attempt;
        }

        $id = $this->create([
            'username' => $candidate,
            'password' => null,
            'email' => '',
            'auth_provider' => 'guest',
        ]);

        return $this->find($id) ?: [
            'id' => $id,
            'username' => $candidate,
            'email' => '',
            'auth_provider' => 'guest',
        ];
    }

    /**
     * Check if username exists
     */
    public function usernameExists(string $username): bool
    {
        return $this->exists(['username' => $username]);
    }

}
