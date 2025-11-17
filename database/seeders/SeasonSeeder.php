<?php

namespace Database\Seeders;

use App\Models\Season;
use App\Models\User;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    public function run(): void
    {
        // Get the test user's default team
        $user = User::where('email', 'user@team.nl')->first();
        if (!$user || !$user->defaultTeam()) {
            return;
        }

        $teamId = $user->defaultTeam()->id;

        $seasons = [
            ['year' => date('Y'), 'part' => 1, 'start' => now(), 'end' => now(), 'formation_id' => 1],
        ];

        foreach ($seasons as $season) {
            Season::create(array_merge($season, [
                'user_id' => $user->id,
                'team_id' => $teamId,
            ]));
        }
    }
}
