<?php

namespace App\Repositories;

use PDO;
use App\Models\Recipe;

class RecipeRepository extends BaseRepository
{
    protected $table = 'recipes';
    protected $fillable = [
        'name',
        'required_materials',
        'result_item',
        'difficulty',
        'time',
        'unlock_level',
    ];

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Create a new recipe
     */
    public function createRecipe(Recipe $recipe): int
    {
        $data = [
            'name' => $recipe->name,
            'required_materials' => $this->encodeJson($recipe->required_materials),
            'result_item' => $recipe->result_item,
            'difficulty' => $recipe->difficulty,
            'time' => $recipe->time,
            'unlock_level' => $recipe->unlock_level,
        ];

        return $this->create($data);
    }

    /**
     * Update an existing recipe
     */
    public function updateRecipe(int $id, Recipe $recipe): bool
    {
        $data = [
            'name' => $recipe->name,
            'required_materials' => $this->encodeJson($recipe->required_materials),
            'result_item' => $recipe->result_item,
            'difficulty' => $recipe->difficulty,
            'time' => $recipe->time,
            'unlock_level' => $recipe->unlock_level,
        ];

        return $this->update($id, $data);
    }

    /**
     * Find recipes by difficulty
     */
    public function findByDifficulty(string $difficulty): array
    {
        $results = $this->findBy(['difficulty' => $difficulty]);
        return array_map([$this, 'mapToModel'], $results);
    }

    /**
     * Find recipes by unlock level
     */
    public function findByUnlockLevel(int $level): array
    {
        $results = $this->findBy(['unlock_level' => $level]);
        return array_map([$this, 'mapToModel'], $results);
    }

    /**
     * Find recipes available for a given level
     */
    public function findAvailableForLevel(int $level): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE unlock_level <= ? ORDER BY unlock_level, difficulty";
        $stmt = $this->query($sql, [$level]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'mapToModel'], $results);
    }

    /**
     * Find all recipes with models
     */
    public function findAllRecipes(): array
    {
        $results = $this->findAll(['unlock_level' => 'ASC', 'name' => 'ASC']);
        return array_map([$this, 'mapToModel'], $results);
    }

    /**
     * Find recipe by ID and return model
     */
    public function findRecipe(int $id): ?Recipe
    {
        $result = $this->find($id);
        return $result ? $this->mapToModel($result) : null;
    }

    /**
     * Map database result to Recipe model
     */
    private function mapToModel(array $data): Recipe
    {
        $data['required_materials'] = $this->decodeJson($data['required_materials'] ?? '[]');
        return Recipe::fromArray($data);
    }
}
