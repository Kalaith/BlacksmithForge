<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;
use App\Http\Request;
use App\Actions\CraftingActions;

class CraftingController {
    public function __construct(private CraftingActions $craftingActions)
    {
    }
    
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
            $result = $this->craftingActions->startCrafting((int) $userId, (int) $recipeId);
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
            $result = $this->craftingActions->processHammerHit((int) $userId, (int) $craftingSessionId, (bool) $accuracy);
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
            $result = $this->craftingActions->completeCrafting((int) $userId, (int) $craftingSessionId, (int) $totalAccuracy);
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
            $result = $this->craftingActions->validateCrafting((int) $userId, (int) $recipeId);
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
            $result = $this->craftingActions->getCraftingSession((int) $userId, (int) $craftingSessionId);
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
        if (!$userId || !$recipeId) {
            $result = [
                'success' => false,
                'message' => 'User ID and Recipe ID are required'
            ];
        } else {
            $result = $this->craftingActions->craft((int) $userId, (int) $recipeId);
        }
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
            $result = $this->craftingActions->history((int) $userId);
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
