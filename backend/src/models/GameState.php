<?php
namespace App\Models;

class GameState {
    public $player;
    public $inventory;
    public $unlockedRecipes;
    public $materials;
    public $forgeUpgrades;
    public $forgeLit;
    public $currentCustomer;
    public $tutorialCompleted;
    public $tutorialStep;

    public function __construct($player, $inventory, $unlockedRecipes, $materials, $forgeUpgrades, $forgeLit, $currentCustomer, $tutorialCompleted, $tutorialStep) {
        $this->player = $player;
        $this->inventory = $inventory;
        $this->unlockedRecipes = $unlockedRecipes;
        $this->materials = $materials;
        $this->forgeUpgrades = $forgeUpgrades;
        $this->forgeLit = $forgeLit;
        $this->currentCustomer = $currentCustomer;
        $this->tutorialCompleted = $tutorialCompleted;
        $this->tutorialStep = $tutorialStep;
    }
}
