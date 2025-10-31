<?php

namespace Database\Seeders;

use App\Models\Player;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    public function run(): void
    {
        $players = [
            ['name' => 'Speler 1', 'position_id' => 2, 'weight' => 1],
            ['name' => 'Speler 2', 'position_id' => 3, 'weight' => 2],
            ['name' => 'Speler 3', 'position_id' => 4, 'weight' => 1],
            ['name' => 'Speler 4', 'position_id' => 2, 'weight' => 1],
            ['name' => 'Speler 5', 'position_id' => 3, 'weight' => 2],
            ['name' => 'Speler 6', 'position_id' => 4, 'weight' => 2],
            ['name' => 'Speler 7', 'position_id' => 2, 'weight' => 1],
            ['name' => 'Speler 8', 'position_id' => 3, 'weight' => 2],
        ];

        foreach ($players as $player) {
            Player::create(array_merge($player, ['user_id' => 2]));
        }
    }
}
