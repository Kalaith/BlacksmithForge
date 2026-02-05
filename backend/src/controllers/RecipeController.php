<?php
namespace App\Controllers;

use App\Http\Response;
use App\Http\Request;

class RecipeController {
    public function getAll(Request $request, Response $response, $args) {
        $result = \App\Actions\RecipeActions::getAll();
        $recipes = array_map(fn($recipe) => $recipe instanceof \App\Models\Recipe ? $recipe->toArray() : $recipe, $result);
        $response->getBody()->write(json_encode(["recipes" => $recipes]));
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function get(Request $request, Response $response, $args) {
        $id = $args['id'] ?? null;
        $result = \App\Actions\RecipeActions::get($id);
        $recipe = $result instanceof \App\Models\Recipe ? $result->toArray() : $result;
        $response->getBody()->write(json_encode(["recipe" => $recipe]));
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
