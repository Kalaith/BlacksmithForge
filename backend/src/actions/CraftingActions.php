<?php
namespace App\Actions;

use App\Services\CraftingService;
use App\Services\InventoryService;
use App\Services\MaterialService;
use App\Services\RecipeService;

class CraftingActions {
    
    /**
     * Start a crafting session
     */
    public static function startCrafting($userId, $recipeId) {
        try {
            $craftingService = new CraftingService();
            $result = $craftingService->startCrafting($userId, $recipeId);
            
            return [
                'success' => true,
                'data' => $result,
                'message' => 'Crafting session started successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Process hammer hit in mini-game
     */
    public static function processHammerHit($userId, $craftingSessionId, $accuracy) {
        try {
            $craftingService = new CraftingService();
            $result = $craftingService->processHammerHit($userId, $craftingSessionId, $accuracy);
            
            return [
                'success' => true,
                'data' => $result,
                'message' => 'Hammer hit processed successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Complete crafting process
     */
    public static function completeCrafting($userId, $craftingSessionId, $totalAccuracy) {
        try {
            $craftingService = new CraftingService();
            $result = $craftingService->completeCrafting($userId, $craftingSessionId, $totalAccuracy);
            
            return [
                'success' => true,
                'data' => $result,
                'message' => 'Crafting completed successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate if user can craft a recipe
     */
    public static function validateCrafting($userId, $recipeId) {
        try {
            $craftingService = new CraftingService();
            $result = $craftingService->validateCrafting($userId, $recipeId);
            
            return [
                'success' => true,
                'data' => $result,
                'message' => 'Crafting validation completed'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get crafting session status
     */
    public static function getCraftingSession($userId, $craftingSessionId) {
        try {
            $craftingService = new CraftingService();
            $result = $craftingService->getCraftingSession($userId, $craftingSessionId);
            
            return [
                'success' => true,
                'data' => $result,
                'message' => 'Crafting session retrieved successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Legacy craft method for backward compatibility
     */
    public static function craft($userId, $recipeId, $materialsUsed) {
        // For now, redirect to the new complete crafting flow
        $startResult = self::startCrafting($userId, $recipeId);
        
        if (!$startResult['success']) {
            return $startResult;
        }
        
        $sessionId = $startResult['data']['session_id'];
        
        // Simulate perfect accuracy for legacy calls
        $completeResult = self::completeCrafting($userId, $sessionId, 100);
        
        return $completeResult;
    }
    
    /**
     * Get crafting history for a user
     */
    public static function history($userId) {
        try {
            $craftingService = new CraftingService();
            $history = $craftingService->getCraftingHistory($userId);
            
            return [
                'success' => true,
                'data' => $history,
                'count' => count($history)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
