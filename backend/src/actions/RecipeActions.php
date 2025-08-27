<?php
namespace App\Actions;

class RecipeActions {
    public static function getAll() {
        return [
            new \App\Models\Recipe('Iron Dagger', ['Iron Ore' => 1, 'Wood' => 1, 'Coal' => 1], 15, 1, '🗡️'),
            new \App\Models\Recipe('Iron Sword', ['Iron Ore' => 2, 'Wood' => 1, 'Leather' => 1, 'Coal' => 2], 35, 2, '⚔️'),
        ];
    }
    public static function get($id) {
        return new \App\Models\Recipe('Iron Dagger', ['Iron Ore' => 1, 'Wood' => 1, 'Coal' => 1], 15, 1, '🗡️');
    }
    public static function create($data) {
        $recipe = new \App\Models\Recipe(
            $data['name'] ?? '',
            $data['materials'] ?? [],
            $data['sellPrice'] ?? 0,
            $data['difficulty'] ?? 1,
            $data['icon'] ?? ''
        );
        return ["created" => true, "recipe" => $recipe];
    }
    public static function update($id, $data) {
        $recipe = new \App\Models\Recipe(
            $data['name'] ?? '',
            $data['materials'] ?? [],
            $data['sellPrice'] ?? 0,
            $data['difficulty'] ?? 1,
            $data['icon'] ?? ''
        );
        return ["updated" => true, "recipe" => $recipe];
    }
    public static function delete($id) {
        return ["deleted" => true];
    }
}
