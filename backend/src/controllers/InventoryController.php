<?php
namespace App\Controllers;

use App\Http\Response;
use App\Http\Request;
use App\Services\InventoryService;
use App\Repositories\InventoryRepository;

class InventoryController {
    private InventoryService $inventoryService;
    private InventoryRepository $inventoryRepository;

    public function __construct(InventoryService $inventoryService, InventoryRepository $inventoryRepository)
    {
        $this->inventoryService = $inventoryService;
        $this->inventoryRepository = $inventoryRepository;
    }

    public function get(Request $request, Response $response, $args) {
        $userId = $this->getAuthUserId($request);
        if (!$userId) {
            return $this->unauthorized($response);
        }

        $items = $this->inventoryService->getUserInventory($userId);
        $payload = [
            'success' => true,
            'data' => $items,
            'count' => count($items)
        ];
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function add(Request $request, Response $response, $args) {
        $userId = $this->getAuthUserId($request);
        if (!$userId) {
            return $this->unauthorized($response);
        }

        $data = $request->getParsedBody();
        if (!$data || !is_array($data)) {
            return $this->badRequest($response, 'Invalid item data');
        }

        $itemId = $this->inventoryRepository->addItem($userId, $data);
        $payload = [
            'success' => true,
            'data' => ['item_id' => $itemId]
        ];
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function remove(Request $request, Response $response, $args) {
        $userId = $this->getAuthUserId($request);
        if (!$userId) {
            return $this->unauthorized($response);
        }

        $data = $request->getParsedBody();
        $itemId = $data['item_id'] ?? null;
        if (!$itemId) {
            return $this->badRequest($response, 'Item ID is required');
        }

        $removed = $this->inventoryRepository->removeItem($userId, $itemId);
        $payload = [
            'success' => $removed,
            'message' => $removed ? 'Item removed' : 'Item not found'
        ];
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function getAuthUserId(Request $request): ?int
    {
        $authUser = $request->getAttribute('auth_user');
        return isset($authUser['id']) ? (int) $authUser['id'] : null;
    }

    private function unauthorized(Response $response): Response
    {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Unauthorized',
        ]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    private function badRequest(Response $response, string $message): Response
    {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => $message,
        ]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }
}
