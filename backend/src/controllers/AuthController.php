<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController {
    public function register(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $result = \App\Actions\AuthActions::register($data);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function login(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $result = \App\Actions\AuthActions::login($data);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function logout(Request $request, Response $response, $args) {
        $userId = $args['user_id'] ?? null;
        $result = \App\Actions\AuthActions::logout($userId);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
