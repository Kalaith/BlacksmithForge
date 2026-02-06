<?php

namespace App\Services;

use App\Repositories\MiniGameRepository;
use Psr\Log\LoggerInterface;

class MiniGameService
{
    private MiniGameRepository $repository;
    private LoggerInterface $logger;

    public function __construct(MiniGameRepository $repository, LoggerInterface $logger)
    {
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function playGame(int $userId, string $gameType, array $gameData): array
    {
        try {
            $score = isset($gameData['score']) ? (int) $gameData['score'] : rand(50, 100);
            $result = $gameData['result'] ?? 'Good job!';

            $this->repository->logGame([
                'user_id' => $userId,
                'game_type' => $gameType,
                'score' => $score,
                'result' => $result
            ]);

            return [
                'success' => true,
                'score' => $score,
                'result' => $result
            ];
        } catch (\Exception $e) {
            $this->logger->error("Failed to play mini-game for user {$userId}: " . $e->getMessage());
            throw new \RuntimeException('Failed to play mini-game');
        }
    }

    public function getHistory(int $userId, int $limit = 50): array
    {
        try {
            return $this->repository->getUserHistory($userId, $limit);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get mini-game history for user {$userId}: " . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve mini-game history');
        }
    }
}
