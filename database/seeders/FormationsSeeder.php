<?php

namespace Database\Seeders;

use App\Models\Formation;
use Illuminate\Database\Seeder;

class FormationsSeeder extends Seeder
{
    public function run(): void
    {
        $formations = [
            ['total_players' => 6, 'lineup_formation' => '2-1-2', 'is_global' => 1],
            ['total_players' => 8, 'lineup_formation' => '3-2-2', 'is_global' => 1],
            ['total_players' => 11, 'lineup_formation' => '4-3-3', 'is_global' => 1],
        ];

        foreach ($formations as $formation) {
            Formation::create($formation);
        }
    }
}
