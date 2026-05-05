<?php

declare(strict_types=1);

namespace App\Actions;

use App\Services\CraftingService;

class CraftingActions
{
    public function __construct(private readonly CraftingService $craftingService)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function startCrafting(int $userId, int $recipeId): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->craftingService->startCrafting($userId, $recipeId),
                'message' => 'Crafting session started successfully',
            ];
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function processHammerHit(int $userId, int $craftingSessionId, bool $accuracy): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->craftingService->processHammerHit($userId, $craftingSessionId, $accuracy),
                'message' => 'Hammer hit processed successfully',
            ];
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function completeCrafting(int $userId, int $craftingSessionId, int $totalAccuracy): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->craftingService->completeCrafting($userId, $craftingSessionId, $totalAccuracy),
                'message' => 'Crafting completed successfully',
            ];
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function validateCrafting(int $userId, int $recipeId): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->craftingService->validateCrafting($userId, $recipeId),
                'message' => 'Crafting validation completed',
            ];
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getCraftingSession(int $userId, int $craftingSessionId): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->craftingService->getCraftingSession($userId, $craftingSessionId),
                'message' => 'Crafting session retrieved successfully',
            ];
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function craft(int $userId, int $recipeId): array
    {
        try {
            return $this->craftingService->craft($userId, $recipeId);
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function history(int $userId): array
    {
        try {
            $history = $this->craftingService->getCraftingHistory($userId);
            return [
                'success' => true,
                'data' => $history,
                'count' => count($history),
            ];
        } catch (\Exception $e) {
            return $this->failure($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function failure(\Exception $e): array
    {
        return [
            'success' => false,
            'message' => $e->getMessage(),
        ];
    }
}
