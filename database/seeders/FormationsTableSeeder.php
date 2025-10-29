<?php

namespace Database\Seeders;

use App\Models\Formation;
use Illuminate\Database\Seeder;

class FormationsTableSeeder extends Seeder
{
    public function run(): void
    {
        $formations = [
            ['total_players' => 6, 'lineup_formation' => '2-1-2'],
            ['total_players' => 8, 'lineup_formation' => '3-2-2'],
            ['total_players' => 11, 'lineup_formation' => '4-3-3'],
        ];

        foreach ($formations as $formation) {
            Formation::create($formation);
        }
    }
}
