<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UpgradeController {
    public function getAll(Request $request, Response $response, $args) {
        $result = \App\Actions\UpgradeActions::getAll();
        $response->getBody()->write(json_encode(["upgrades" => $result]));
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function purchase(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $userId = $data['user_id'] ?? null;
        $upgradeId = $data['upgrade_id'] ?? null;
        $result = \App\Actions\UpgradeActions::purchase($userId, $upgradeId);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
