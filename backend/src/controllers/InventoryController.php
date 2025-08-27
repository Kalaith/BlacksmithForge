<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class InventoryController {
    public function get(Request $request, Response $response, $args) {
        $userId = $args['user_id'] ?? null;
        $result = \App\Actions\InventoryActions::getInventory($userId);
        $response->getBody()->write(json_encode(["inventory" => $result]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function add(Request $request, Response $response, $args) {
        $userId = $args['user_id'] ?? null;
        $data = $request->getParsedBody();
        $itemId = $data['item_id'] ?? null;
        $quantity = $data['quantity'] ?? 1;
        $result = \App\Actions\InventoryActions::addItem($userId, $itemId, $quantity);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function remove(Request $request, Response $response, $args) {
        $userId = $args['user_id'] ?? null;
        $data = $request->getParsedBody();
        $itemId = $data['item_id'] ?? null;
        $quantity = $data['quantity'] ?? 1;
        $result = \App\Actions\InventoryActions::removeItem($userId, $itemId, $quantity);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
