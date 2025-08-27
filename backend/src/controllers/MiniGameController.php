<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MiniGameController {
    public function play(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $userId = $data['user_id'] ?? null;
        $gameType = $data['game_type'] ?? null;
        $score = $data['score'] ?? null;
        $result = \App\Actions\MiniGameActions::play($userId, $gameType, $score);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function history(Request $request, Response $response, $args) {
        $userId = $args['user_id'] ?? null;
        $result = \App\Actions\MiniGameActions::history($userId);
        $response->getBody()->write(json_encode(["history" => $result]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
