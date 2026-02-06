<?php
namespace App\Controllers;

use App\Http\Response;
use App\Http\Request;
use App\Services\MiniGameService;

class MiniGameController {
    public function __construct(private MiniGameService $miniGameService) {}

    public function play(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $userId = $this->getAuthUserId($request);
        if (!$userId) {
            return $this->unauthorized($response);
        }

        $gameType = $data['gameType'] ?? $data['game_type'] ?? 'unknown';
        $score = $data['score'] ?? null;
        $result = $this->miniGameService->playGame($userId, $gameType, [
            'score' => $score,
            'result' => $data['result'] ?? null
        ]);

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function history(Request $request, Response $response, $args) {
        $userId = $this->getAuthUserId($request);
        if (!$userId) {
            return $this->unauthorized($response);
        }

        $result = $this->miniGameService->getHistory($userId);
        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $result,
            'count' => count($result)
        ]));
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
}
