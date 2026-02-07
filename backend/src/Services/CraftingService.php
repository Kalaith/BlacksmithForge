<?php

namespace App\Services;

use App\Repositories\CraftingRepository;
use App\Repositories\RecipeRepository;
use App\Repositories\InventoryRepository;
use App\Repositories\MaterialRepository;
use App\Services\GameConfigService;
use Psr\Log\LoggerInterface;

class CraftingService
{
    private CraftingRepository $craftingRepository;
    private RecipeRepository $recipeRepository;
    private InventoryRepository $inventoryRepository;
    private MaterialRepository $materialRepository;
    private GameConfigService $configService;
    private LoggerInterface $logger;

    // Quality thresholds and constants
    private const DEFAULT_QUALITY_THRESHOLDS = [
        'excellent' => 80,
        'good' => 60,
        'fair' => 40,
        'poor' => 0
    ];
    
    private const DEFAULT_MAX_HAMMER_CLICKS = 4;
    
    private const DEFAULT_QUALITY_MULTIPLIERS = [
        'Excellent' => 1.2,
        'Good' => 1.1,
        'Fair' => 1.0,
        'Poor' => 0.8
    ];

    private ?array $qualityThresholds = null;
    private ?array $qualityMultipliers = null;
    private ?int $maxHammerClicks = null;

    public function __construct(
        CraftingRepository $craftingRepository,
        RecipeRepository $recipeRepository,
        InventoryRepository $inventoryRepository,
        MaterialRepository $materialRepository,
        GameConfigService $configService,
        LoggerInterface $logger
    ) {
        $this->craftingRepository = $craftingRepository;
        $this->recipeRepository = $recipeRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->materialRepository = $materialRepository;
        $this->configService = $configService;
        $this->logger = $logger;
    }

    /**
     * Start a new crafting session
     */
    public function startCrafting(int $userId, int $recipeId): array
    {
        try {
            $recipe = $this->getRecipeOrFail($recipeId);

            // Validate user has required materials
            $validation = $this->validateCrafting($userId, $recipeId);
            if (!$validation['can_craft']) {
                throw new \RuntimeException($validation['message']);
            }

            // Create crafting session
            $sessionData = [
                'user_id' => $userId,
                'recipe_id' => $recipeId,
                'status' => 'in_progress',
                'hammer_clicks' => 0,
                'total_accuracy' => 0,
                'started_at' => date('Y-m-d H:i:s'),
                'materials_consumed' => json_encode($recipe['required_materials'])
            ];

            $sessionId = $this->craftingRepository->createSession($sessionData);

            // Reserve materials (mark as used but don't delete yet)
            $this->reserveMaterials($userId, $recipe['required_materials']);

            $this->logger->info("Crafting session started", [
                'user_id' => $userId,
                'recipe_id' => $recipeId,
                'session_id' => $sessionId
            ]);

            return [
                'session_id' => $sessionId,
                'recipe' => $recipe,
                'max_hammer_clicks' => $this->getMaxHammerClicks(),
                'quality_thresholds' => $this->getQualityThresholds(),
                'status' => 'started'
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to start crafting", [
                'user_id' => $userId,
                'recipe_id' => $recipeId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process a hammer hit during the mini-game
     */
    public function processHammerHit(int $userId, int $sessionId, bool $hitSuccess): array
    {
        try {
            $session = $this->craftingRepository->getSession($sessionId, $userId);
            if (!$session || $session['status'] !== 'in_progress') {
                throw new \InvalidArgumentException('Invalid crafting session');
            }

            if ($session['hammer_clicks'] >= $this->getMaxHammerClicks()) {
                throw new \RuntimeException('Maximum hammer clicks reached');
            }

            // Calculate accuracy increase (25 points per successful hit)
            $accuracyIncrease = $hitSuccess ? 25 : 0;
            $newHammerClicks = $session['hammer_clicks'] + 1;
            $newTotalAccuracy = $session['total_accuracy'] + $accuracyIncrease;

            // Update session
            $this->craftingRepository->updateSession($sessionId, [
                'hammer_clicks' => $newHammerClicks,
                'total_accuracy' => $newTotalAccuracy
            ]);

            $maxClicks = $this->getMaxHammerClicks();
            $isComplete = $newHammerClicks >= $maxClicks;

            $this->logger->info("Hammer hit processed", [
                'session_id' => $sessionId,
                'hit_success' => $hitSuccess,
                'new_accuracy' => $newTotalAccuracy,
                'clicks_remaining' => $maxClicks - $newHammerClicks
            ]);

            return [
                'hit_success' => $hitSuccess,
                'accuracy_increase' => $accuracyIncrease,
                'total_accuracy' => $newTotalAccuracy,
                'hammer_clicks' => $newHammerClicks,
                'max_clicks' => $maxClicks,
                'is_complete' => $isComplete,
                'remaining_clicks' => $maxClicks - $newHammerClicks
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to process hammer hit", [
                'session_id' => $sessionId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Complete the crafting process
     */
    public function completeCrafting(int $userId, int $sessionId, int $totalAccuracy): array
    {
        try {
            $session = $this->getSessionOrFail($userId, $sessionId);
            $recipe = $this->getRecipeOrFail($session['recipe_id']);

            // Determine quality based on accuracy
            $quality = $this->calculateQuality($totalAccuracy);
            
            // Calculate final item value
            $finalValue = $this->calculateFinalValue($recipe, $quality);

            // Create the crafted item
            $craftedItem = $this->buildCraftedItem($recipe, $quality, $finalValue);

            // Add item to user's inventory
            $itemId = $this->inventoryRepository->addItem($userId, $craftedItem);

            // Finalize material consumption
            $this->consumeReservedMaterials($userId, json_decode($session['materials_consumed'], true));

            // Mark session as completed
            $this->craftingRepository->updateSession($sessionId, [
                'status' => 'completed',
                'final_accuracy' => $totalAccuracy,
                'result_quality' => $quality,
                'result_value' => $finalValue,
                'completed_at' => date('Y-m-d H:i:s'),
                'item_id' => $itemId
            ]);

            $this->logger->info("Crafting completed successfully", [
                'user_id' => $userId,
                'session_id' => $sessionId,
                'quality' => $quality,
                'value' => $finalValue
            ]);

            return [
                'success' => true,
                'quality' => $quality,
                'item' => $craftedItem,
                'message' => "{$quality} quality item crafted!",
                'accuracy_achieved' => $totalAccuracy,
                'value_earned' => $finalValue,
                'session_id' => $sessionId
            ];

        } catch (\Exception $e) {
            // Mark session as failed
            $this->craftingRepository->updateSession($sessionId, [
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            $this->logger->error("Failed to complete crafting", [
                'session_id' => $sessionId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Validate if user can craft a recipe
     */
    public function validateCrafting(int $userId, int $recipeId): array
    {
        try {
            $recipe = $this->recipeRepository->findById($recipeId);
            if (!$recipe) {
                return [
                    'can_craft' => false,
                    'message' => 'Recipe not found'
                ];
            }

            $userMaterials = $this->inventoryRepository->getUserMaterials($userId);
            $requiredMaterials = $recipe['required_materials'];
            $missingMaterials = [];

            foreach ($requiredMaterials as $material) {
                $materialName = $material['name'];
                $requiredQuantity = $material['quantity'];
                $userQuantity = $userMaterials[$materialName] ?? 0;

                if ($userQuantity < $requiredQuantity) {
                    $missingMaterials[] = [
                        'name' => $materialName,
                        'required' => $requiredQuantity,
                        'available' => $userQuantity,
                        'missing' => $requiredQuantity - $userQuantity
                    ];
                }
            }

            $canCraft = empty($missingMaterials);

            return [
                'can_craft' => $canCraft,
                'recipe' => $recipe,
                'user_materials' => $userMaterials,
                'missing_materials' => $missingMaterials,
                'message' => $canCraft ? 'Ready to craft' : 'Insufficient materials'
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to validate crafting", [
                'user_id' => $userId,
                'recipe_id' => $recipeId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get crafting session details
     */
    public function getCraftingSession(int $userId, int $sessionId): array
    {
        try {
            $session = $this->craftingRepository->getSession($sessionId, $userId);
            if (!$session) {
                throw new \InvalidArgumentException('Crafting session not found');
            }

            return [
                'session' => $session,
                'status' => $session['status'],
                'progress' => [
                    'hammer_clicks' => $session['hammer_clicks'],
                    'max_clicks' => $this->getMaxHammerClicks(),
                    'total_accuracy' => $session['total_accuracy'],
                    'remaining_clicks' => $this->getMaxHammerClicks() - $session['hammer_clicks']
                ]
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to get crafting session", [
                'session_id' => $sessionId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get crafting history for a user
     */
    public function getCraftingHistory(int $userId, int $limit = 50): array
    {
        try {
            return $this->craftingRepository->getUserHistory($userId, $limit);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get crafting history", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate quality based on accuracy percentage
     */
    private function calculateQuality(int $accuracy): string
    {
        $thresholds = $this->getQualityThresholds();
        if ($accuracy >= $thresholds['excellent']) {
            return 'Excellent';
        } elseif ($accuracy >= $thresholds['good']) {
            return 'Good';
        } elseif ($accuracy >= $thresholds['fair']) {
            return 'Fair';
        } else {
            return 'Poor';
        }
    }

    private function getRecipeOrFail(int $recipeId): array
    {
        $recipe = $this->recipeRepository->findById($recipeId);
        if (!$recipe) {
            throw new \InvalidArgumentException('Recipe not found');
        }
        return $recipe;
    }

    private function getSessionOrFail(int $userId, int $sessionId): array
    {
        $session = $this->craftingRepository->getSession($sessionId, $userId);
        if (!$session || $session['status'] !== 'in_progress') {
            throw new \InvalidArgumentException('Invalid crafting session');
        }
        return $session;
    }

    private function calculateFinalValue(array $recipe, string $quality): int
    {
        $baseValue = $recipe['sell_price'] ?? 100;
        $qualityMultiplier = $this->getQualityMultipliers()[$quality] ?? 1.0;
        return (int) floor($baseValue * $qualityMultiplier);
    }

    private function buildCraftedItem(array $recipe, string $quality, int $finalValue): array
    {
        $stats = $this->calculateItemStats($recipe, $quality);
        return [
            'name' => $recipe['name'],
            'type' => $stats['type'],
            'quality' => $quality,
            'value' => $finalValue,
            'icon' => $recipe['icon'] ?? '⚒️',
            'stats' => $stats['values'],
            'crafted_at' => date('Y-m-d H:i:s'),
            'recipe_id' => $recipe['id']
        ];
    }

    /**
     * Derive item stats based on recipe and quality.
     */
    private function calculateItemStats(array $recipe, string $quality): array
    {
        $difficulty = $recipe['difficulty'] ?? 'easy';
        $resultItem = strtolower($recipe['result_item'] ?? '');

        $baseByDifficulty = [
            'easy' => 10,
            'medium' => 20,
            'hard' => 35,
            'expert' => 60
        ];

        $base = $baseByDifficulty[$difficulty] ?? 10;
        $multiplier = $this->getQualityMultipliers()[$quality] ?? 1.0;

        $type = 'tool';
        $stats = [];

        if (str_contains($resultItem, 'sword') || str_contains($resultItem, 'dagger') || str_contains($resultItem, 'axe') || str_contains($resultItem, 'mace')) {
            $type = 'weapon';
            $stats['attack'] = (int) floor($base * $multiplier);
            $stats['durability'] = (int) floor(($base + 20) * $multiplier);
        } elseif (str_contains($resultItem, 'shield') || str_contains($resultItem, 'armor') || str_contains($resultItem, 'helmet') || str_contains($resultItem, 'chest')) {
            $type = 'armor';
            $stats['defense'] = (int) floor($base * $multiplier);
            $stats['durability'] = (int) floor(($base + 25) * $multiplier);
        } else {
            $type = 'tool';
            $stats['speed'] = (int) floor($base * $multiplier);
            $stats['durability'] = (int) floor(($base + 15) * $multiplier);
        }

        return [
            'type' => $type,
            'values' => $stats
        ];
    }

    /**
     * Reserve materials for crafting (mark as pending use)
     */
    private function reserveMaterials(int $userId, array $materials): void
    {
        foreach ($materials as $material) {
            $this->inventoryRepository->reserveMaterial(
                $userId,
                $material['name'],
                $material['quantity']
            );
        }
    }

    /**
     * Consume reserved materials (permanently remove)
     */
    private function consumeReservedMaterials(int $userId, array $materials): void
    {
        foreach ($materials as $material) {
            $this->inventoryRepository->consumeMaterial(
                $userId,
                $material['name'],
                $material['quantity']
            );
        }
    }

    /**
     * Legacy craft method for backward compatibility
     */
    public function craft(int $userId, int $recipeId): array
    {
        try {
            // Start crafting session
            $startResult = $this->startCrafting($userId, $recipeId);
            $sessionId = $startResult['session_id'];

            // Simulate perfect crafting (100% accuracy)
            $completeResult = $this->completeCrafting($userId, $sessionId, 100);

            return [
                'success' => true,
                'message' => 'Crafting completed',
                'result' => $completeResult
            ];
        } catch (\Exception $e) {
            $this->logger->error("Failed to craft for user {$userId}: " . $e->getMessage());
            throw new \RuntimeException('Failed to craft item');
        }
    }

    private function getQualityThresholds(): array
    {
        if ($this->qualityThresholds !== null) {
            return $this->qualityThresholds;
        }

        $value = $this->configService->getValue('crafting_quality_thresholds', self::DEFAULT_QUALITY_THRESHOLDS);
        if (!is_array($value)) {
            $value = self::DEFAULT_QUALITY_THRESHOLDS;
        }

        $this->qualityThresholds = array_merge(self::DEFAULT_QUALITY_THRESHOLDS, $value);
        return $this->qualityThresholds;
    }

    private function getQualityMultipliers(): array
    {
        if ($this->qualityMultipliers !== null) {
            return $this->qualityMultipliers;
        }

        $value = $this->configService->getValue('crafting_quality_multipliers', self::DEFAULT_QUALITY_MULTIPLIERS);
        if (!is_array($value)) {
            $value = self::DEFAULT_QUALITY_MULTIPLIERS;
        }

        $this->qualityMultipliers = array_merge(self::DEFAULT_QUALITY_MULTIPLIERS, $value);
        return $this->qualityMultipliers;
    }

    private function getMaxHammerClicks(): int
    {
        if ($this->maxHammerClicks !== null) {
            return $this->maxHammerClicks;
        }

        $value = $this->configService->getValue('crafting_max_hammer_clicks', self::DEFAULT_MAX_HAMMER_CLICKS);
        $maxClicks = (int) $value;
        if ($maxClicks <= 0) {
            $maxClicks = self::DEFAULT_MAX_HAMMER_CLICKS;
        }

        $this->maxHammerClicks = $maxClicks;
        return $this->maxHammerClicks;
    }
}
