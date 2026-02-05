<?php

namespace App\Models;

class User
{
    public ?int $id;
    public string $username;
    public string $password;
    public string $email;
    public ?string $auth_provider;
    public ?\DateTime $created_at;
    public ?\DateTime $updated_at;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->username = $data['username'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->auth_provider = $data['auth_provider'] ?? null;
        $this->created_at = isset($data['created_at']) ? new \DateTime($data['created_at']) : null;
        $this->updated_at = isset($data['updated_at']) ? new \DateTime($data['updated_at']) : null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'auth_provider' => $this->auth_provider,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public function validate(): array
    {
        $errors = [];

        if (empty($this->username)) {
            $errors[] = 'Username is required';
        }

        if (strlen($this->username) < 3) {
            $errors[] = 'Username must be at least 3 characters long';
        }

        if (strlen($this->username) > 50) {
            $errors[] = 'Username must be less than 50 characters';
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $this->username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores';
        }

        if (empty($this->password)) {
            $errors[] = 'Password is required';
        }

        if (strlen($this->password) < 6) {
            $errors[] = 'Password must be at least 6 characters long';
        }

        return $errors;
    }

    public function hashPassword(): void
    {
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
}
