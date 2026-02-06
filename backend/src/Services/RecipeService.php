<?php

namespace App\Services;

use App\Repositories\RecipeRepository;
use App\Repositories\MaterialRepository;
use App\Models\Recipe;
use Psr\Log\LoggerInterface;

class RecipeService
{
    private RecipeRepository $repository;
    private MaterialRepository $materialRepository;
    private LoggerInterface $logger;

    public function __construct(RecipeRepository $repository, MaterialRepository $materialRepository, LoggerInterface $logger)
    {
        $this->repository = $repository;
        $this->materialRepository = $materialRepository;
        $this->logger = $logger;
    }

    public function getAllRecipes(): array
    {
        try {
            return $this->repository->findAllRecipes();
        } catch (\Exception $e) {
            $this->logger->error('Failed to get all recipes: ' . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve recipes');
        }
    }

    public function getRecipeById(int $id): ?Recipe
    {
        try {
            return $this->repository->findRecipe($id);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get recipe {$id}: " . $e->getMessage());
            throw new \RuntimeException('Failed to retrieve recipe');
        }
    }

    public function createRecipe(array $data): Recipe
    {
        $recipe = new Recipe([
            'name' => $data['name'] ?? '',
            'required_materials' => $data['materials'] ?? $data['required_materials'] ?? [],
            'result_item' => $data['result_item'] ?? '',
            'difficulty' => $data['difficulty'] ?? 'easy',
            'time' => $data['time'] ?? 60,
            'unlock_level' => $data['unlock_level'] ?? 1
        ]);

        $errors = $recipe->validate();
        if (!empty($errors)) {
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errors));
        }

        try {
            $id = $this->repository->createRecipe($recipe);
            $recipe->id = $id;
            return $recipe;
        } catch (\Exception $e) {
            $this->logger->error('Failed to create recipe: ' . $e->getMessage());
            throw new \RuntimeException('Failed to create recipe');
        }
    }

    public function updateRecipe(int $id, array $data): ?Recipe
    {
        $existing = $this->repository->findRecipe($id);
        if (!$existing) {
            return null;
        }

        $recipe = new Recipe(array_merge($existing->toArray(), [
            'name' => $data['name'] ?? $existing->name,
            'required_materials' => $data['materials'] ?? $data['required_materials'] ?? $existing->required_materials,
            'result_item' => $data['result_item'] ?? $existing->result_item,
            'difficulty' => $data['difficulty'] ?? $existing->difficulty,
            'time' => $data['time'] ?? $existing->time,
            'unlock_level' => $data['unlock_level'] ?? $existing->unlock_level
        ]));

        $errors = $recipe->validate();
        if (!empty($errors)) {
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errors));
        }

        try {
            $success = $this->repository->updateRecipe($id, $recipe);
            return $success ? $recipe : null;
        } catch (\Exception $e) {
            $this->logger->error("Failed to update recipe {$id}: " . $e->getMessage());
            throw new \RuntimeException('Failed to update recipe');
        }
    }

    public function deleteRecipe(int $id): bool
    {
        try {
            return $this->repository->delete($id);
        } catch (\Exception $e) {
            $this->logger->error("Failed to delete recipe {$id}: " . $e->getMessage());
            throw new \RuntimeException('Failed to delete recipe');
        }
    }
}
