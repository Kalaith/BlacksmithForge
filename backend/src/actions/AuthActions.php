<?php
namespace App\Actions;

class AuthActions {
    public static function register($data) {
        // Example: create a new Player model for the registered user
        $player = new \App\Models\Player(100, 0, 1, 0); // Starting values
        return ["registered" => true, "player" => $player];
    }
    public static function login($data) {
        // Example: return a Player model for the logged-in user
        $player = new \App\Models\Player(100, 0, 1, 0); // Example values
        return ["logged_in" => true, "player" => $player];
    }
    public static function logout($userId) {
        return ["logged_out" => true];
    }
}
