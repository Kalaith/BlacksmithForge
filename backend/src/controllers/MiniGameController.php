<?php
namespace App\Controllers;

use App\Http\Response;
use App\Http\Request;

class MiniGameController {
    public function play(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $userId = $this->getAuthUserId($request);
        $gameType = $data['game_type'] ?? null;
        $score = $data['score'] ?? null;
        $result = \App\Actions\MiniGameActions::play($userId, $gameType, $score);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function history(Request $request, Response $response, $args) {
        $userId = $this->getAuthUserId($request);
        $result = \App\Actions\MiniGameActions::history($userId);
        $response->getBody()->write(json_encode(["history" => $result]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function getAuthUserId(Request $request): ?int
    {
        $authUser = $request->getAttribute('auth_user');
        return isset($authUser['id']) ? (int) $authUser['id'] : null;
    }
}
