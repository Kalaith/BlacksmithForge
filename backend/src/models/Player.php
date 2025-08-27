<?php
namespace App\Models;

class Player {
    public $gold;
    public $reputation;
    public $level;
    public $experience;

    public function __construct($gold = 0, $reputation = 0, $level = 1, $experience = 0) {
        $this->gold = $gold;
        $this->reputation = $reputation;
        $this->level = $level;
        $this->experience = $experience;
    }
}
