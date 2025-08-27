<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RecipeController {
    public function getAll(Request $request, Response $response, $args) {
        $result = \App\Actions\RecipeActions::getAll();
        $response->getBody()->write(json_encode(["recipes" => $result]));
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function get(Request $request, Response $response, $args) {
        $id = $args['id'] ?? null;
        $result = \App\Actions\RecipeActions::get($id);
        $response->getBody()->write(json_encode(["recipe" => $result]));
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function create(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $result = \App\Actions\RecipeActions::create($data);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function update(Request $request, Response $response, $args) {
        $id = $args['id'] ?? null;
        $data = $request->getParsedBody();
        $result = \App\Actions\RecipeActions::update($id, $data);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function delete(Request $request, Response $response, $args) {
        $id = $args['id'] ?? null;
        $result = \App\Actions\RecipeActions::delete($id);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
