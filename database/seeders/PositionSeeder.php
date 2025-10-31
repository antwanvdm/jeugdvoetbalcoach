<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        // Preserve fixed IDs used in LineupGeneratorService constants
        $positions = [
            ['id' => 1, 'name' => 'Keeper'],
            ['id' => 2, 'name' => 'Verdediger'],
            ['id' => 3, 'name' => 'Middenvelder'],
            ['id' => 4, 'name' => 'Aanvaller'],
        ];

        foreach ($positions as $position) {
            Position::create($position);
        }
    }
}
