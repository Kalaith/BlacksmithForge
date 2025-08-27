<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\AuthRepository;
use Psr\Log\LoggerInterface;

class AuthService
{
    private AuthRepository $repository;
    private LoggerInterface $logger;

    public function __construct(AuthRepository $repository, LoggerInterface $logger)
    {
        $this->repository = $repository;
        $this->logger = $logger;
    }

    /**
     * Register a new user
     */
    public function register(array $data): User
    {
        $user = new User($data);
        
        $errors = $user->validate();
        if (!empty($errors)) {
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errors));
        }

        // Check if username already exists
        if ($this->repository->usernameExists($user->username)) {
            throw new \InvalidArgumentException('Username already exists');
        }

        try {
            // Hash the password
            $user->hashPassword();
            
            $id = $this->repository->createUser($user);
            $user->id = $id;
            
            $this->logger->info("User registered with ID {$id}");
            return $user;
        } catch (\Exception $e) {
            $this->logger->error('Failed to register user: ' . $e->getMessage());
            throw new \RuntimeException('Failed to register user');
        }
    }

    /**
     * Login user
     */
    public function login(string $username, string $password): ?User
    {
        try {
            $user = $this->repository->findByUsername($username);
            
            if (!$user) {
                $this->logger->warning("Login attempt with non-existent username: {$username}");
                return null;
            }

            if (!$user->verifyPassword($password)) {
                $this->logger->warning("Invalid password for username: {$username}");
                return null;
            }

            $this->logger->info("User logged in: {$username}");
            return $user;
        } catch (\Exception $e) {
            $this->logger->error('Failed to login user: ' . $e->getMessage());
            throw new \RuntimeException('Failed to login user');
        }
    }

    /**
     * Get user by ID
     */
    public function getUserById(int $id): ?User
    {
        try {
            return $this->repository->findUser($id);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get user {$id}: " . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve user');
        }
    }

    /**
     * Check if username is available
     */
    public function isUsernameAvailable(string $username): bool
    {
        try {
            return !$this->repository->usernameExists($username);
        } catch (\Exception $e) {
            $this->logger->error('Failed to check username availability: ' . $e->getMessage());
            throw new \RuntimeException('Failed to check username availability');
        }
    }
}
