<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            OpponentSeeder::class,
            UserSeeder::class,
            PositionSeeder::class,
            FormationSeeder::class,
            SeasonSeeder::class,
            PlayerSeeder::class,
        ]);
    }
}
