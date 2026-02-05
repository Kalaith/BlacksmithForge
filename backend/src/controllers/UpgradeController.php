<?php
namespace App\Controllers;

use App\Http\Response;
use App\Http\Request;

class UpgradeController {
    public function getAll(Request $request, Response $response, $args) {
        $result = \App\Actions\UpgradeActions::getAll();
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function purchase(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $userId = $this->getAuthUserId($request);
        $upgradeId = $data['upgrade_id'] ?? null;
        $result = \App\Actions\UpgradeActions::purchase($userId, $upgradeId);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function getAuthUserId(Request $request): ?int
    {
        $authUser = $request->getAttribute('auth_user');
        return isset($authUser['id']) ? (int) $authUser['id'] : null;
    }
}
