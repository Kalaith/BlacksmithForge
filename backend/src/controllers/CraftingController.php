<?php
namespace App\Controllers;

use App\Http\Response;
use App\Http\Request;
use App\Actions\CraftingActions;

class CraftingController {
    
    /**
     * Start a new crafting session
     */
    public function startCrafting(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $userId = $this->getAuthUserId($request);
        $recipeId = $data['recipe_id'] ?? null;
        
        if (!$userId || !$recipeId) {
            $result = [
                'success' => false,
                'message' => 'User ID and Recipe ID are required'
            ];
        } else {
            $result = CraftingActions::startCrafting($userId, $recipeId);
        }
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Process a hammer hit during crafting mini-game
     */
    public function processHammerHit(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $userId = $this->getAuthUserId($request);
        $craftingSessionId = $data['crafting_session_id'] ?? null;
        $accuracy = $data['accuracy'] ?? 0;
        
        if (!$userId || !$craftingSessionId) {
            $result = [
                'success' => false,
                'message' => 'User ID and Crafting Session ID are required'
            ];
        } else {
            $result = CraftingActions::processHammerHit($userId, $craftingSessionId, $accuracy);
        }
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Complete crafting process
     */
    public function completeCrafting(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $userId = $this->getAuthUserId($request);
        $craftingSessionId = $data['crafting_session_id'] ?? null;
        $totalAccuracy = $data['total_accuracy'] ?? 0;
        
        if (!$userId || !$craftingSessionId) {
            $result = [
                'success' => false,
                'message' => 'User ID and Crafting Session ID are required'
            ];
        } else {
            $result = CraftingActions::completeCrafting($userId, $craftingSessionId, $totalAccuracy);
        }
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Validate if user can craft a recipe
     */
    public function validateCrafting(Request $request, Response $response, $args) {
        $userId = $this->getAuthUserId($request);
        $recipeId = $args['recipe_id'] ?? null;
        
        if (!$userId || !$recipeId) {
            $result = [
                'success' => false,
                'message' => 'User ID and Recipe ID are required'
            ];
        } else {
            $result = CraftingActions::validateCrafting($userId, $recipeId);
        }
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Get crafting session status
     */
    public function getCraftingSession(Request $request, Response $response, $args) {
        $userId = $this->getAuthUserId($request);
        $craftingSessionId = $args['crafting_session_id'] ?? null;
        
        if (!$userId || !$craftingSessionId) {
            $result = [
                'success' => false,
                'message' => 'User ID and Crafting Session ID are required'
            ];
        } else {
            $result = CraftingActions::getCraftingSession($userId, $craftingSessionId);
        }
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Legacy craft method for backward compatibility
     */
    public function craft(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $userId = $this->getAuthUserId($request);
        $recipeId = $data['recipe_id'] ?? null;
        $materialsUsed = $data['materials_used'] ?? [];
        
        $result = CraftingActions::craft($userId, $recipeId, $materialsUsed);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Get crafting history for a user
     */
    public function history(Request $request, Response $response, $args) {
        $userId = $this->getAuthUserId($request);
        
        if (!$userId) {
            $result = [
                'success' => false,
                'message' => 'User ID is required'
            ];
        } else {
            $result = CraftingActions::history($userId);
        }
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function getAuthUserId(Request $request): ?int
    {
        $authUser = $request->getAttribute('auth_user');
        return isset($authUser['id']) ? (int) $authUser['id'] : null;
    }
}
