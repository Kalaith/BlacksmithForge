<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;
use App\Http\Request;
use App\Actions\CustomerActions;

class CustomerController {
    public function __construct(private CustomerActions $customerActions)
    {
    }
    
    /**
     * Get all customers
     */
    public function getAll(Request $request, Response $response, $args) {
        $result = $this->customerActions->getAll();
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Get specific customer
     */
    public function get(Request $request, Response $response, $args) {
        $id = (int) ($args['id'] ?? 0);
        $result = $this->customerActions->get($id);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Get current customer for a user
     */
    public function getCurrentCustomer(Request $request, Response $response, $args) {
        $userId = $this->getAuthUserId($request);
        
        if (!$userId) {
            $result = [
                'success' => false,
                'message' => 'User ID is required'
            ];
        } else {
            $result = $this->customerActions->getCurrentCustomer((int) $userId);
        }
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Generate new customer for a user
     */
    public function generateCustomer(Request $request, Response $response, $args) {
        $userId = $this->getAuthUserId($request);
        
        if (!$userId) {
            $result = [
                'success' => false,
                'message' => 'User ID is required'
            ];
        } else {
            $result = $this->customerActions->generateCustomer((int) $userId);
        }
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Sell item to customer
     */
    public function sellItem(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $userId = $this->getAuthUserId($request);
        $itemId = $data['item_id'] ?? null;
        $customerId = $data['customer_id'] ?? null;
        
        if (!$userId || !$itemId || !$customerId) {
            $result = [
                'success' => false,
                'message' => 'User ID, Item ID, and Customer ID are required'
            ];
        } else {
            $result = $this->customerActions->sellItem((int) $userId, (int) $itemId, (int) $customerId);
        }
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Get selling price for an item
     */
    public function getSellingPrice(Request $request, Response $response, $args) {
        $userId = $this->getAuthUserId($request);
        $itemId = $args['item_id'] ?? null;
        $customerId = $args['customer_id'] ?? null;
        
        if (!$userId || !$itemId || !$customerId) {
            $result = [
                'success' => false,
                'message' => 'User ID, Item ID, and Customer ID are required'
            ];
        } else {
            $result = $this->customerActions->getSellingPrice((int) $userId, (int) $itemId, (int) $customerId);
        }
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    /**
     * Dismiss current customer
     */
    public function dismissCustomer(Request $request, Response $response, $args) {
        $userId = $this->getAuthUserId($request);
        
        if (!$userId) {
            $result = [
                'success' => false,
                'message' => 'User ID is required'
            ];
        } else {
            $result = $this->customerActions->dismissCustomer((int) $userId);
        }
        
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function getAuthUserId(Request $request): ?int
    {
        $authUser = $request->getAttribute('auth_user');
        return isset($authUser['id']) ? (int) $authUser['id'] : null;
    }
    public function create(Request $request, Response $response, $args) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Customer type management is not available through this game API'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(405);
    }
    
    public function update(Request $request, Response $response, $args) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Customer type management is not available through this game API'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(405);
    }
    
    public function delete(Request $request, Response $response, $args) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Customer type management is not available through this game API'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(405);
    }
}
