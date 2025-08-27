<?php

namespace App\Models;

class Recipe
{
    public ?int $id;
    public string $name;
    public array $required_materials;
    public string $result_item;
    public string $difficulty;
    public int $time;
    public int $unlock_level;
    public ?\DateTime $created_at;
    public ?\DateTime $updated_at;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->required_materials = is_string($data['required_materials'] ?? '') 
            ? json_decode($data['required_materials'], true) ?? []
            : ($data['required_materials'] ?? []);
        $this->result_item = $data['result_item'] ?? '';
        $this->difficulty = $data['difficulty'] ?? 'easy';
        $this->time = $data['time'] ?? 60;
        $this->unlock_level = $data['unlock_level'] ?? 1;
        $this->created_at = isset($data['created_at']) ? new \DateTime($data['created_at']) : null;
        $this->updated_at = isset($data['updated_at']) ? new \DateTime($data['updated_at']) : null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'required_materials' => $this->required_materials,
            'result_item' => $this->result_item,
            'difficulty' => $this->difficulty,
            'time' => $this->time,
            'unlock_level' => $this->unlock_level,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public function validate(): array
    {
        $errors = [];

        if (empty($this->name)) {
            $errors[] = 'Name is required';
        }

        if (strlen($this->name) > 100) {
            $errors[] = 'Name must be less than 100 characters';
        }

        if (empty($this->required_materials)) {
            $errors[] = 'Required materials cannot be empty';
        }

        if (empty($this->result_item)) {
            $errors[] = 'Result item is required';
        }

        if (!in_array($this->difficulty, ['easy', 'medium', 'hard', 'expert'])) {
            $errors[] = 'Invalid difficulty level';
        }

        if ($this->time <= 0) {
            $errors[] = 'Time must be greater than 0';
        }

        if ($this->unlock_level < 1) {
            $errors[] = 'Unlock level must be at least 1';
        }

        return $errors;
    }

    public function getDifficultyMultiplier(): float
    {
        return match ($this->difficulty) {
            'easy' => 1.0,
            'medium' => 1.5,
            'hard' => 2.0,
            'expert' => 3.0,
            default => 1.0,
        };
    }

    public function getTotalMaterialCost(): int
    {
        $total = 0;
        foreach ($this->required_materials as $material) {
            $total += ($material['quantity'] ?? 1) * ($material['cost'] ?? 0);
        }
        return $total;
    }
}
