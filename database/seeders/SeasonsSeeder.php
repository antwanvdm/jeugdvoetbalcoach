<?php

namespace Database\Seeders;

use App\Models\Season;
use Illuminate\Database\Seeder;

class SeasonsSeeder extends Seeder
{
    public function run(): void
    {
        $seasons = [
            ['year' => date('Y'), 'part' => 1, 'start' => now(), 'end' => now(), 'formation_id' => 1],
        ];

        foreach ($seasons as $season) {
            Season::create($season);
        }
    }
}
