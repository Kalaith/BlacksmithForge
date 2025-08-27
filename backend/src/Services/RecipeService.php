<?php

namespace App\Services;

use App\Repositories\RecipeRepository;
use App\Repositories\MaterialRepository;
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

    // Add other recipe methods as needed
}
