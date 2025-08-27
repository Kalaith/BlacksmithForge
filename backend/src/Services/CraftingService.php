<?php

namespace App\Services;

use App\Repositories\CraftingRepository;
use App\Repositories\RecipeRepository;
use App\Repositories\InventoryRepository;
use App\Repositories\MaterialRepository;
use Psr\Log\LoggerInterface;

class CraftingService
{
    private CraftingRepository $craftingRepository;
    private RecipeRepository $recipeRepository;
    private InventoryRepository $inventoryRepository;
    private MaterialRepository $materialRepository;
    private LoggerInterface $logger;

    // Quality thresholds and constants
    private const QUALITY_THRESHOLDS = [
        'excellent' => 80,
        'good' => 60,
        'fair' => 40,
        'poor' => 0
    ];
    
    private const MAX_HAMMER_CLICKS = 4;
    
    private const QUALITY_MULTIPLIERS = [
        'Excellent' => 1.2,
        'Good' => 1.1,
        'Fair' => 1.0,
        'Poor' => 0.8
    ];

    public function __construct(
        CraftingRepository $craftingRepository,
        RecipeRepository $recipeRepository,
        InventoryRepository $inventoryRepository,
        MaterialRepository $materialRepository,
        LoggerInterface $logger
    ) {
        $this->craftingRepository = $craftingRepository;
        $this->recipeRepository = $recipeRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->materialRepository = $materialRepository;
        $this->logger = $logger;
    }

    /**
     * Start a new crafting session
     */
    public function startCrafting(int $userId, int $recipeId): array
    {
        try {
            // Validate the recipe exists
            $recipe = $this->recipeRepository->findById($recipeId);
            if (!$recipe) {
                throw new \InvalidArgumentException('Recipe not found');
            }

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
                'max_hammer_clicks' => self::MAX_HAMMER_CLICKS,
                'quality_thresholds' => self::QUALITY_THRESHOLDS,
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

            if ($session['hammer_clicks'] >= self::MAX_HAMMER_CLICKS) {
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

            $isComplete = $newHammerClicks >= self::MAX_HAMMER_CLICKS;

            $this->logger->info("Hammer hit processed", [
                'session_id' => $sessionId,
                'hit_success' => $hitSuccess,
                'new_accuracy' => $newTotalAccuracy,
                'clicks_remaining' => self::MAX_HAMMER_CLICKS - $newHammerClicks
            ]);

            return [
                'hit_success' => $hitSuccess,
                'accuracy_increase' => $accuracyIncrease,
                'total_accuracy' => $newTotalAccuracy,
                'hammer_clicks' => $newHammerClicks,
                'max_clicks' => self::MAX_HAMMER_CLICKS,
                'is_complete' => $isComplete,
                'remaining_clicks' => self::MAX_HAMMER_CLICKS - $newHammerClicks
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
            $session = $this->craftingRepository->getSession($sessionId, $userId);
            if (!$session || $session['status'] !== 'in_progress') {
                throw new \InvalidArgumentException('Invalid crafting session');
            }

            $recipe = $this->recipeRepository->findById($session['recipe_id']);
            if (!$recipe) {
                throw new \RuntimeException('Recipe not found');
            }

            // Determine quality based on accuracy
            $quality = $this->calculateQuality($totalAccuracy);
            
            // Calculate final item value
            $baseValue = $recipe['sell_price'] ?? 100;
            $qualityMultiplier = self::QUALITY_MULTIPLIERS[$quality];
            $finalValue = (int) floor($baseValue * $qualityMultiplier);

            // Create the crafted item
            $craftedItem = [
                'name' => $recipe['name'],
                'type' => 'weapon',
                'quality' => $quality,
                'value' => $finalValue,
                'icon' => $recipe['icon'] ?? '⚒️',
                'crafted_at' => date('Y-m-d H:i:s'),
                'recipe_id' => $recipe['id']
            ];

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
                    'max_clicks' => self::MAX_HAMMER_CLICKS,
                    'total_accuracy' => $session['total_accuracy'],
                    'remaining_clicks' => self::MAX_HAMMER_CLICKS - $session['hammer_clicks']
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
        if ($accuracy >= self::QUALITY_THRESHOLDS['excellent']) {
            return 'Excellent';
        } elseif ($accuracy >= self::QUALITY_THRESHOLDS['good']) {
            return 'Good';
        } elseif ($accuracy >= self::QUALITY_THRESHOLDS['fair']) {
            return 'Fair';
        } else {
            return 'Poor';
        }
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
}
