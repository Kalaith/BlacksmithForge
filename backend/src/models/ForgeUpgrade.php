<?php
namespace App\Models;

class ForgeUpgrade {
    public $name;
    public $cost;
    public $effect;
    public $icon;

    public function __construct($name, $cost, $effect, $icon) {
        $this->name = $name;
        $this->cost = $cost;
        $this->effect = $effect;
        $this->icon = $icon;
    }
}
