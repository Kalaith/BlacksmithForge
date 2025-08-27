<?php

namespace App\Repositories;

use PDO;
use App\Models\Material;

class MaterialRepository extends BaseRepository
{
    protected $table = 'materials';
    protected $fillable = [
        'name',
        'type',
        'rarity',
        'quantity',
        'properties',
    ];

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * Create a new material
     */
    public function createMaterial(Material $material): int
    {
        $data = [
            'name' => $material->name,
            'type' => $material->type,
            'rarity' => $material->rarity,
            'quantity' => $material->quantity,
            'properties' => $this->encodeJson($material->properties),
        ];

        return $this->create($data);
    }

    /**
     * Update an existing material
     */
    public function updateMaterial(int $id, Material $material): bool
    {
        $data = [
            'name' => $material->name,
            'type' => $material->type,
            'rarity' => $material->rarity,
            'quantity' => $material->quantity,
            'properties' => $this->encodeJson($material->properties),
        ];

        return $this->update($id, $data);
    }

    /**
     * Find materials by type
     */
    public function findByType(string $type): array
    {
        $results = $this->findBy(['type' => $type]);
        return array_map([$this, 'mapToModel'], $results);
    }

    /**
     * Find materials by rarity
     */
    public function findByRarity(string $rarity): array
    {
        $results = $this->findBy(['rarity' => $rarity]);
        return array_map([$this, 'mapToModel'], $results);
    }

    /**
     * Find all materials with models
     */
    public function findAllMaterials(): array
    {
        $results = $this->findAll();
        return array_map([$this, 'mapToModel'], $results);
    }

    /**
     * Find material by ID and return model
     */
    public function findMaterial(int $id): ?Material
    {
        $result = $this->find($id);
        return $result ? $this->mapToModel($result) : null;
    }

    /**
     * Update material quantity
     */
    public function updateQuantity(int $id, int $quantity): bool
    {
        return $this->update($id, ['quantity' => $quantity]);
    }

    /**
     * Map database result to Material model
     */
    private function mapToModel(array $data): Material
    {
        $data['properties'] = $this->decodeJson($data['properties'] ?? '[]');
        return Material::fromArray($data);
    }
}
