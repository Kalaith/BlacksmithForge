<?php
namespace App\Actions;

class RecipeActions {
    public static function getAll() {
        return [
            new \App\Models\Recipe([
                'name' => 'Iron Dagger',
                'required_materials' => [
                    ['name' => 'Iron Ore', 'quantity' => 1],
                    ['name' => 'Wood', 'quantity' => 1],
                    ['name' => 'Coal', 'quantity' => 1],
                ],
                'result_item' => 'Iron Dagger',
                'difficulty' => 'easy',
                'time' => 60,
                'unlock_level' => 1,
            ]),
            new \App\Models\Recipe([
                'name' => 'Iron Sword',
                'required_materials' => [
                    ['name' => 'Iron Ore', 'quantity' => 2],
                    ['name' => 'Wood', 'quantity' => 1],
                    ['name' => 'Leather', 'quantity' => 1],
                    ['name' => 'Coal', 'quantity' => 2],
                ],
                'result_item' => 'Iron Sword',
                'difficulty' => 'medium',
                'time' => 120,
                'unlock_level' => 2,
            ]),
        ];
    }
    public static function get($id) {
        return new \App\Models\Recipe([
            'name' => 'Iron Dagger',
            'required_materials' => [
                ['name' => 'Iron Ore', 'quantity' => 1],
                ['name' => 'Wood', 'quantity' => 1],
                ['name' => 'Coal', 'quantity' => 1],
            ],
            'result_item' => 'Iron Dagger',
            'difficulty' => 'easy',
            'time' => 60,
            'unlock_level' => 1,
        ]);
    }
    public static function create($data) {
        $recipe = new \App\Models\Recipe([
            'name' => $data['name'] ?? '',
            'required_materials' => $data['materials'] ?? [],
            'result_item' => $data['result_item'] ?? '',
            'difficulty' => $data['difficulty'] ?? 'easy',
            'time' => $data['time'] ?? 60,
            'unlock_level' => $data['unlock_level'] ?? 1,
        ]);
        return ["created" => true, "recipe" => $recipe];
    }
    public static function update($id, $data) {
        $recipe = new \App\Models\Recipe([
            'name' => $data['name'] ?? '',
            'required_materials' => $data['materials'] ?? [],
            'result_item' => $data['result_item'] ?? '',
            'difficulty' => $data['difficulty'] ?? 'easy',
            'time' => $data['time'] ?? 60,
            'unlock_level' => $data['unlock_level'] ?? 1,
        ]);
        return ["updated" => true, "recipe" => $recipe];
    }
    public static function delete($id) {
        return ["deleted" => true];
    }
}
