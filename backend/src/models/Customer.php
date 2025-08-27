<?php

namespace App\Models;

class Customer
{
    public ?int $id;
    public string $name;
    public string $avatar;
    public string $order;
    public int $patience;
    public int $reward;
    public string $status;
    public ?\DateTime $created_at;
    public ?\DateTime $updated_at;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->avatar = $data['avatar'] ?? '';
        $this->order = $data['order'] ?? '';
        $this->patience = $data['patience'] ?? 100;
        $this->reward = $data['reward'] ?? 0;
        $this->status = $data['status'] ?? 'waiting';
        $this->created_at = isset($data['created_at']) ? new \DateTime($data['created_at']) : null;
        $this->updated_at = isset($data['updated_at']) ? new \DateTime($data['updated_at']) : null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'order' => $this->order,
            'patience' => $this->patience,
            'reward' => $this->reward,
            'status' => $this->status,
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

        if (empty($this->order)) {
            $errors[] = 'Order is required';
        }

        if ($this->patience < 0 || $this->patience > 100) {
            $errors[] = 'Patience must be between 0 and 100';
        }

        if ($this->reward < 0) {
            $errors[] = 'Reward cannot be negative';
        }

        if (!in_array($this->status, ['waiting', 'in_progress', 'completed', 'cancelled'])) {
            $errors[] = 'Invalid status';
        }

        return $errors;
    }

    public function isPatient(): bool
    {
        return $this->patience > 20;
    }

    public function decreasePatience(int $amount = 10): void
    {
        $this->patience = max(0, $this->patience - $amount);
        if ($this->patience <= 0) {
            $this->status = 'cancelled';
        }
    }

    public function completeOrder(): void
    {
        $this->status = 'completed';
        $this->patience = 100;
    }
}
