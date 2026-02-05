<?php

namespace App\Models;

class BlacksmithProfile
{
    public ?int $id;
    public int $user_id;
    public string $forge_name;
    public int $level;
    public int $reputation;
    public int $coins;
    public ?array $crafting_mastery;
    public ?array $settings;
    public ?string $last_seen_at;
    public ?string $created_at;
    public ?string $updated_at;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->user_id = (int) ($data['user_id'] ?? 0);
        $this->forge_name = $data['forge_name'] ?? 'New Forge';
        $this->level = (int) ($data['level'] ?? 1);
        $this->reputation = (int) ($data['reputation'] ?? 0);
        $this->coins = (int) ($data['coins'] ?? 0);
        $this->crafting_mastery = $data['crafting_mastery'] ?? null;
        $this->settings = $data['settings'] ?? null;
        $this->last_seen_at = $data['last_seen_at'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'forge_name' => $this->forge_name,
            'level' => $this->level,
            'reputation' => $this->reputation,
            'coins' => $this->coins,
            'crafting_mastery' => $this->crafting_mastery,
            'settings' => $this->settings,
            'last_seen_at' => $this->last_seen_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
