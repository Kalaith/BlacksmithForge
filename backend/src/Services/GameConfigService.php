<?php

namespace App\Services;

use App\Repositories\GameConfigRepository;
use Psr\Log\LoggerInterface;

class GameConfigService
{
    private GameConfigRepository $repository;
    private LoggerInterface $logger;

    public function __construct(GameConfigRepository $repository, LoggerInterface $logger)
    {
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function getValue(string $key, mixed $default = null): mixed
    {
        try {
            $value = $this->repository->getValue($key);
            return $value ?? $default;
        } catch (\Exception $e) {
            $this->logger->error("Failed to read config {$key}: " . $e->getMessage());
            return $default;
        }
    }
}
