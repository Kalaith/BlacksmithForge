<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;
use App\Http\Request;
use App\Actions\UpgradeActions;

class UpgradeController {
    public function __construct(private UpgradeActions $upgradeActions)
    {
    }

    public function getAll(Request $request, Response $response, $args) {
        $result = $this->upgradeActions->getAll();
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getPurchased(Request $request, Response $response, $args) {
        $userId = $this->getAuthUserId($request);
        $result = $userId
            ? $this->upgradeActions->getPurchased((int) $userId)
            : ['success' => false, 'message' => 'User ID is required'];
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function purchase(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $userId = $this->getAuthUserId($request);
        $upgradeId = $data['upgrade_id'] ?? null;
        $result = $userId && $upgradeId
            ? $this->upgradeActions->purchase((int) $userId, (int) $upgradeId)
            : ['success' => false, 'message' => 'User ID and Upgrade ID are required'];
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function getAuthUserId(Request $request): ?int
    {
        $authUser = $request->getAttribute('auth_user');
        return isset($authUser['id']) ? (int) $authUser['id'] : null;
    }
}
