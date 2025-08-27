<?php
namespace App\Actions;

class MiniGameActions {
    public static function play($userId, $gameType, $score) {
        $player = new \App\Models\Player(100, 0, 1, 0);
        return ["played" => true, "player" => $player];
    }
    public static function history($userId) {
        return [
            new \App\Models\Player(100, 0, 1, 0),
            new \App\Models\Player(120, 1, 2, 50),
        ];
    }
}
