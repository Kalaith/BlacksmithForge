<?php

namespace App\Models;

class Material
{
    public ?int $id;
    public string $name;
    public string $type;
    public string $rarity;
    public int $quantity;
    public array $properties;
    public ?\DateTime $created_at;
    public ?\DateTime $updated_at;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->type = $data['type'] ?? 'ore';
        $this->rarity = $data['rarity'] ?? 'common';
        $this->quantity = $data['quantity'] ?? 0;
        $this->properties = $data['properties'] ?? [];
        $this->created_at = isset($data['created_at']) ? new \DateTime($data['created_at']) : null;
        $this->updated_at = isset($data['updated_at']) ? new \DateTime($data['updated_at']) : null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'rarity' => $this->rarity,
            'quantity' => $this->quantity,
            'properties' => $this->properties,
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

        if (!in_array($this->type, ['ore', 'gem', 'wood', 'metal', 'misc'])) {
            $errors[] = 'Invalid material type';
        }

        if (!in_array($this->rarity, ['common', 'uncommon', 'rare', 'epic', 'legendary'])) {
            $errors[] = 'Invalid rarity level';
        }

        if ($this->quantity < 0) {
            $errors[] = 'Quantity cannot be negative';
        }

        return $errors;
    }

    public function getRarityValue(): int
    {
        return match ($this->rarity) {
            'common' => 1,
            'uncommon' => 2,
            'rare' => 3,
            'epic' => 4,
            'legendary' => 5,
            default => 1,
        };
    }
}
