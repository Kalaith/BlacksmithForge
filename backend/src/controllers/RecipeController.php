<?php
namespace App\Controllers;

use App\Http\Response;
use App\Http\Request;
use App\Services\RecipeService;

class RecipeController {
    public function __construct(private RecipeService $recipeService) {}

    public function getAll(Request $request, Response $response, $args) {
        $recipes = $this->recipeService->getAllRecipes();
        $payload = [
            'success' => true,
            'data' => array_map(fn($recipe) => $recipe->toArray(), $recipes),
            'count' => count($recipes)
        ];
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function get(Request $request, Response $response, $args) {
        $id = (int) ($args['id'] ?? 0);
        if ($id <= 0) {
            return $this->errorResponse($response, 'Invalid recipe ID', 400);
        }

        $recipe = $this->recipeService->getRecipeById($id);
        if (!$recipe) {
            return $this->errorResponse($response, 'Recipe not found', 404);
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => $recipe->toArray()
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        if (!$data || !is_array($data)) {
            return $this->errorResponse($response, 'Invalid request data', 400);
        }

        try {
            $recipe = $this->recipeService->createRecipe($data);
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $recipe->toArray()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($response, $e->getMessage(), 400);
        }
    }

    public function update(Request $request, Response $response, $args) {
        $id = (int) ($args['id'] ?? 0);
        if ($id <= 0) {
            return $this->errorResponse($response, 'Invalid recipe ID', 400);
        }

        $data = $request->getParsedBody();
        if (!$data || !is_array($data)) {
            return $this->errorResponse($response, 'Invalid request data', 400);
        }

        try {
            $recipe = $this->recipeService->updateRecipe($id, $data);
            if (!$recipe) {
                return $this->errorResponse($response, 'Recipe not found', 404);
            }
            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $recipe->toArray()
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($response, $e->getMessage(), 400);
        }
    }

    public function delete(Request $request, Response $response, $args) {
        $id = (int) ($args['id'] ?? 0);
        if ($id <= 0) {
            return $this->errorResponse($response, 'Invalid recipe ID', 400);
        }

        $success = $this->recipeService->deleteRecipe($id);
        if (!$success) {
            return $this->errorResponse($response, 'Recipe not found', 404);
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Recipe deleted'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function errorResponse(Response $response, string $message, int $status): Response
    {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => $message
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
