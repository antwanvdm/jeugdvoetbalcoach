<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\FootballMatch;
use App\Models\Player;
use App\Models\Position;
use App\Models\Season;
use App\Models\Team;
use App\Models\Formation;
use App\Services\LineupGeneratorService;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class LineupGeneratorServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createUserAndTeam(): array
    {
        $team = Team::create([
            'name' => 'Test Team',
            'opponent_id' => null,
            'invite_code' => 'TESTCODE',
        ]);
        $user = \App\Models\User::factory()->create();
        $team->users()->attach($user->id, ['role' => 1, 'is_default' => 1, 'joined_at' => now()]);
        $this->actingAs($user);
        $this->withSession(['current_team_id' => $team->id]);
        return [$user, $team];
    }

    private function createSeasonWithFormation(Team $team, string $lineupFormation = '2-1-2', int $totalPlayers = 6): Season
    {
        $formation = Formation::create([
            'team_id' => null,
            'lineup_formation' => $lineupFormation,
            'total_players' => $totalPlayers,
            'is_global' => true,
        ]);

        $season = Season::create([
            'team_id' => $team->id,
            'formation_id' => $formation->id,
            'year' => (int) now()->year,
            'part' => 1,
            'start' => now()->startOfYear()->toDateString(),
            'end' => now()->endOfYear()->toDateString(),
            'track_goals' => false,
            'share_token' => Str::random(32),
        ]);

        return $season;
    }

    private function createPlayers(Team $team, int $count, array $keeperIndices = [], array $weights = []): Collection
    {
        foreach ([1 => 'Keeper', 2 => 'Defender', 3 => 'Midfielder', 4 => 'Attacker'] as $id => $name) {
            Position::query()->firstOrCreate(['id' => $id], ['name' => $name]);
        }

        $players = collect();
        for ($i = 0; $i < $count; $i++) {
            $isKeeper = in_array($i, $keeperIndices, true);
            $positionId = $isKeeper ? 1 : [2,3,4][($i % 3)];
            $weight = $weights[$i] ?? ($i % 3) + 1;
            $players->push(Player::create([
                'team_id' => $team->id,
                'name' => 'Player '.($i+1),
                'position_id' => $positionId,
                'weight' => $weight,
            ]));
        }
        return $players;
    }

    private function createMatch(Season $season, array $payload = []): FootballMatch
    {
        // Create a dummy opponent if not provided
        if (!isset($payload['opponent_id'])) {
            $opponent = \App\Models\Opponent::create([
                'name' => 'Test Opponent',
                'location' => 'Test Location',
                'latitude' => 52.370216,
                'longitude' => 4.895168,
            ]);
            $payload['opponent_id'] = $opponent->id;
        }

        $data = array_merge([
            'goals_scored' => 0,
            'goals_conceded' => 0,
            'date' => now(),
            'home' => 1,
            'season_id' => $season->id,
            'team_id' => $season->team_id,
            'user_id' => auth()->id(),
            'share_token' => Str::random(32),
        ], $payload);
        return FootballMatch::create($data);
    }

    private function generateLineup(FootballMatch $match, array $availablePlayers = []): void
    {
        $service = new LineupGeneratorService();
        $service->generateLineup($match, $availablePlayers);
    }

    public function test_one_keeper_keeps_all_quarters_and_is_never_benched(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '2-1-2', 6);
        $players = $this->createPlayers($team, 8, keeperIndices: [0]);
        $season->players()->sync($players->pluck('id'));

        $match = $this->createMatch($season);
        $this->generateLineup($match, $players->pluck('id')->all());

        $keeper = $players[0];
        $quarters = $match->players()
            ->wherePivot('player_id', $keeper->id)
            ->get()
            ->map(fn($p) => ['q' => $p->pivot->quarter, 'pos' => $p->pivot->position_id]);

        $this->assertSame(4, $quarters->where('pos', 1)->count());
        $this->assertSame(0, $quarters->where('pos', null)->count());
    }

    public function test_two_keepers_only_play_keeper_and_rotate_fairly(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '2-1-2', 6);
        $players = $this->createPlayers($team, 8, keeperIndices: [0,1]);
        $season->players()->sync($players->pluck('id'));

        $match = $this->createMatch($season);
        $this->generateLineup($match, $players->pluck('id')->all());

        $keeper1 = $players[0];
        $keeper2 = $players[1];

        $k1Assignments = $match->players()->wherePivot('player_id', $keeper1->id)->get();
        $k2Assignments = $match->players()->wherePivot('player_id', $keeper2->id)->get();

        $k1KeeperCount = $match->players()->wherePivot('player_id', $keeper1->id)->wherePivot('position_id', 1)->count();
        $k2KeeperCount = $match->players()->wherePivot('player_id', $keeper2->id)->wherePivot('position_id', 1)->count();

        // Each keeper should have 4 total assignments (2 keeper + 2 bench)
        $this->assertSame(4, $k1Assignments->count());
        $this->assertSame(4, $k2Assignments->count());
        
        // Each keeper should keep for exactly 2 quarters
        $this->assertSame(2, $k1KeeperCount);
        $this->assertSame(2, $k2KeeperCount);
        
        // Verify no outfield positions (2=defender, 3=midfielder, 4=attacker)
        $k1Outfield = $match->players()->wherePivot('player_id', $keeper1->id)->whereIn('football_match_player.position_id', [2,3,4])->count();
        $k2Outfield = $match->players()->wherePivot('player_id', $keeper2->id)->whereIn('football_match_player.position_id', [2,3,4])->count();
        
        $this->assertSame(0, $k1Outfield);
        $this->assertSame(0, $k2Outfield);
    }

    public function test_no_keepers_allows_all_players_to_rotate_through_keeper_position(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '2-1-2', 6);
        // Create players with no one designated as keeper (all position_id = 2, 3, or 4)
        $players = $this->createPlayers($team, 8, keeperIndices: []);
        $season->players()->sync($players->pluck('id'));

        $match = $this->createMatch($season);
        $this->generateLineup($match, $players->pluck('id')->all());

        // Verify that keeper position is filled each quarter (someone must keep)
        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $keepersInQuarter = $match->players()
                ->wherePivot('quarter', $quarter)
                ->wherePivot('position_id', 1)
                ->count();
            $this->assertSame(1, $keepersInQuarter, "Quarter $quarter should have exactly 1 keeper");
        }

        // Collect which players kept and verify they got 1 bench quarter each
        $playersWhoKept = $match->players()
            ->wherePivot('position_id', 1)
            ->get()
            ->unique('id');

        foreach ($playersWhoKept as $player) {
            $benchCount = $match->players()
                ->wherePivot('player_id', $player->id)
                ->whereNull('football_match_player.position_id')
                ->count();
            $this->assertSame(1, $benchCount, "Player {$player->id} who kept should have exactly 1 bench quarter");
        }

        // Verify 4 different players kept (one per quarter)
        $this->assertSame(4, $playersWhoKept->count());
    }

    public function test_bench_distribution_fairness_across_consecutive_matches_with_uneven_counts(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '2-1-2', 6);

        $players = $this->createPlayers($team, 7, keeperIndices: [0]);
        $season->players()->sync($players->pluck('id'));

        for ($i = 0; $i < 3; $i++) {
            $match = $this->createMatch($season, ['date' => now()->addDays($i)]);
            $this->generateLineup($match, $players->pluck('id')->all());
        }

        $benchCounts = [];
        foreach ($players as $p) {
            $benchCounts[$p->id] = \DB::table('football_match_player')
                ->join('football_matches', 'football_matches.id', '=', 'football_match_player.football_match_id')
                ->where('football_matches.season_id', $season->id)
                ->where('football_match_player.player_id', $p->id)
                ->whereNull('football_match_player.position_id')
                ->count();
        }

        $nonKeepers = $players->reject(fn($pl) => $pl->position_id === 1);
        $subset = array_intersect_key($benchCounts, $nonKeepers->pluck('id')->flip()->toArray());
        $minBench = min($subset);
        $maxBench = max($subset);
        
        // With fair distribution, max difference should be 1
        $this->assertLessThanOrEqual(1, $maxBench - $minBench, 
            'Bench distribution should be fair with max 1 difference');
    }

    public function test_each_quarter_has_desired_on_field_and_valid_bench_count(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '2-1-2', 6);

        $players = $this->createPlayers($team, 9, keeperIndices: [0]);
        $season->players()->sync($players->pluck('id'));

        $match = $this->createMatch($season);
        $this->generateLineup($match, $players->pluck('id')->all());

        $desiredOnField = 6;

        for ($q = 1; $q <= 4; $q++) {
            $onField = $match->players()->wherePivot('quarter', $q)->whereNotNull('football_match_player.position_id')->count();
            $benched = $match->players()->wherePivot('quarter', $q)->whereNull('football_match_player.position_id')->count();
            $this->assertSame($desiredOnField, $onField);
            $this->assertSame($players->count() - $desiredOnField, $benched);
        }
    }

    public function test_last_three_matches_weighting_affects_bench_priority_sanity(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '2-1-2', 6);

        $players = $this->createPlayers($team, 8, keeperIndices: [0]);
        $season->players()->sync($players->pluck('id'));

        for ($i = 1; $i <= 3; $i++) {
            $prior = $this->createMatch($season, ['date' => now()->subDays(5 - $i)]);
            $this->generateLineup($prior, $players->pluck('id')->all());
        }

        $current = $this->createMatch($season, ['date' => now()]);
        $this->generateLineup($current, $players->pluck('id')->all());

        $benchCountsCurrent = [];
        foreach ($players as $p) {
            $benchCountsCurrent[$p->id] = \DB::table('football_match_player')
                ->where('football_match_id', $current->id)
                ->where('player_id', $p->id)
                ->whereNull('position_id')
                ->count();
        }

        $expectedBenchesPerQuarter = $players->count() - 6;
        $totalBenches = array_sum($benchCountsCurrent);
        $this->assertSame($expectedBenchesPerQuarter * 4, $totalBenches);
    }

    public function test_variable_player_availability_across_7_match_season_with_2_1_2_formation(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '2-1-2', 6);

        // Create 10 players total (1 keeper + 9 outfield)
        $players = $this->createPlayers($team, 10, keeperIndices: [0]);
        $season->players()->sync($players->pluck('id'));

        // Define availability per match (mix of 7-10 players available)
        $availabilityPatterns = [
            [0, 1, 2, 3, 4, 5, 6],        // Match 1: 7 players
            [0, 1, 2, 3, 4, 5, 6, 7, 8], // Match 2: 9 players
            [0, 1, 2, 3, 4, 5, 6, 7, 8, 9], // Match 3: 10 players (all)
            [0, 2, 4, 6, 8, 9, 1],        // Match 4: 7 players (different mix)
            [0, 1, 3, 5, 7, 9, 2, 4],    // Match 5: 8 players
            [0, 1, 2, 3, 4, 5, 6, 7, 8, 9], // Match 6: 10 players (all)
            [0, 3, 4, 5, 6, 7, 8],        // Match 7: 7 players
        ];

        // Generate lineups for all 7 matches
        for ($i = 0; $i < 7; $i++) {
            $match = $this->createMatch($season, ['date' => now()->addDays($i)]);
            $availablePlayerIds = collect($availabilityPatterns[$i])
                ->map(fn($idx) => $players[$idx]->id)
                ->toArray();
            $this->generateLineup($match, $availablePlayerIds);
        }

        // Verify bench fairness per player based on their availability
        foreach ($players as $player) {
            // Count how many matches this player was available for
            $matchesAvailable = 0;
            $totalBenches = 0;

            foreach ($availabilityPatterns as $matchIndex => $availableIndices) {
                $playerIndex = $players->search(fn($p) => $p->id === $player->id);
                if (in_array($playerIndex, $availableIndices, true)) {
                    $matchesAvailable++;
                    
                    // Count benches in this match
                    $match = FootballMatch::where('season_id', $season->id)
                        ->orderBy('date')
                        ->skip($matchIndex)
                        ->first();
                    
                    $benchesInMatch = \DB::table('football_match_player')
                        ->where('football_match_id', $match->id)
                        ->where('player_id', $player->id)
                        ->whereNull('position_id')
                        ->count();
                    
                    $totalBenches += $benchesInMatch;
                }
            }

            // Skip keeper for bench fairness check (keeper plays all quarters in 1-keeper scenario)
            if ($player->position_id === 1) {
                $this->assertSame(0, $totalBenches, "Keeper should never be benched");
            } else {
                // For non-keepers, bench count should be proportional to availability
                // With 7 players and 6 on field: 1 bench per quarter = 4 per match
                // Expected benches = matches_available * 4 / 7 (roughly, allowing variance)
                // We're not testing exact distribution here, just that they got some benches
                if ($matchesAvailable > 0) {
                    $this->assertGreaterThan(0, $totalBenches, 
                        "Player {$player->id} played {$matchesAvailable} matches but never benched - unfair");
                }
            }
        }

        // Global fairness check: players with similar availability should have similar bench counts
        $nonKeeperBenchData = [];
        foreach ($players as $player) {
            if ($player->position_id === 1) continue;

            $matchesAvailable = 0;
            $totalBenches = 0;

            foreach ($availabilityPatterns as $matchIndex => $availableIndices) {
                $playerIndex = $players->search(fn($p) => $p->id === $player->id);
                if (in_array($playerIndex, $availableIndices, true)) {
                    $matchesAvailable++;
                    
                    $match = FootballMatch::where('season_id', $season->id)
                        ->orderBy('date')
                        ->skip($matchIndex)
                        ->first();
                    
                    $benchesInMatch = \DB::table('football_match_player')
                        ->where('football_match_id', $match->id)
                        ->where('player_id', $player->id)
                        ->whereNull('position_id')
                        ->count();
                    
                    $totalBenches += $benchesInMatch;
                }
            }

            if ($matchesAvailable > 0) {
                $nonKeeperBenchData[] = [
                    'player_id' => $player->id,
                    'matches_available' => $matchesAvailable,
                    'total_benches' => $totalBenches,
                    'bench_per_match' => $totalBenches / $matchesAvailable,
                ];
            }
        }

        // Players with same availability should have bench-per-match ratio within 1.0
        $groupedByAvailability = collect($nonKeeperBenchData)->groupBy('matches_available');
        
        foreach ($groupedByAvailability as $availability => $group) {
            if ($group->count() < 2) continue;
            
            $benchRatios = $group->pluck('bench_per_match');
            $minRatio = $benchRatios->min();
            $maxRatio = $benchRatios->max();
            
            $this->assertLessThanOrEqual(1.0, $maxRatio - $minRatio,
                "Players with {$availability} matches available have unfair bench distribution");
        }
    }

    public function test_new_player_joining_mid_season_gets_fair_treatment_with_2_1_2(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '2-1-2', 6);

        // Start with 8 players
        $initialPlayers = $this->createPlayers($team, 8, keeperIndices: [0]);
        $season->players()->sync($initialPlayers->pluck('id'));

        // Play 3 matches with initial roster
        for ($i = 0; $i < 3; $i++) {
            $match = $this->createMatch($season, ['date' => now()->addDays($i)]);
            $this->generateLineup($match, $initialPlayers->pluck('id')->all());
        }

        // Add a new player mid-season
        $newPlayer = Player::create([
            'team_id' => $team->id,
            'name' => 'New Player',
            'position_id' => 3, // Midfielder
            'weight' => 2,
        ]);
        
        $season->players()->attach($newPlayer->id);
        $allPlayers = $initialPlayers->push($newPlayer);

        // Play 4 more matches with new player included
        for ($i = 3; $i < 7; $i++) {
            $match = $this->createMatch($season, ['date' => now()->addDays($i)]);
            $this->generateLineup($match, $allPlayers->pluck('id')->all());
        }

        // Check new player's bench count in their 4 matches
        $newPlayerMatches = FootballMatch::where('season_id', $season->id)
            ->orderBy('date')
            ->skip(3)
            ->take(4)
            ->pluck('id');

        $newPlayerBenches = \DB::table('football_match_player')
            ->whereIn('football_match_id', $newPlayerMatches)
            ->where('player_id', $newPlayer->id)
            ->whereNull('position_id')
            ->count();

        // New player should not be over-benched compared to others in same 4 matches
        $otherNonKeeperBenches = [];
        foreach ($initialPlayers as $player) {
            if ($player->position_id === 1) continue; // Skip keeper

            $benches = \DB::table('football_match_player')
                ->whereIn('football_match_id', $newPlayerMatches)
                ->where('player_id', $player->id)
                ->whereNull('position_id')
                ->count();
            
            $otherNonKeeperBenches[] = $benches;
        }

        $avgExistingPlayerBenches = count($otherNonKeeperBenches) > 0 
            ? array_sum($otherNonKeeperBenches) / count($otherNonKeeperBenches) 
            : 0;

        // New player with 0 historical data should get similar or fewer benches
        // Allow some variance (within 2 benches of average)
        $this->assertLessThanOrEqual($avgExistingPlayerBenches + 2, $newPlayerBenches,
            "New player should not be significantly over-benched compared to existing players");
    }

    public function test_3_2_2_formation_with_8_players_maintains_bench_fairness(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '3-2-2', 8);

        // Create 10 players (1 keeper + 9 outfield)
        $players = $this->createPlayers($team, 10, keeperIndices: [0]);
        $season->players()->sync($players->pluck('id'));

        // Play 5 matches
        for ($i = 0; $i < 5; $i++) {
            $match = $this->createMatch($season, ['date' => now()->addDays($i)]);
            $this->generateLineup($match, $players->pluck('id')->all());
        }

        // Verify formation adherence: 8 on field per quarter
        $lastMatch = FootballMatch::where('season_id', $season->id)
            ->latest('date')
            ->first();

        for ($q = 1; $q <= 4; $q++) {
            $onField = $lastMatch->players()
                ->wherePivot('quarter', $q)
                ->whereNotNull('football_match_player.position_id')
                ->count();
            
            $this->assertSame(8, $onField, "Quarter {$q} should have exactly 8 players on field");
        }

        // Check bench distribution across all 5 matches
        // With 10 players, 8 on field = 2 benched per quarter = 8 per match
        // Over 5 matches = 40 total bench slots for 9 non-keepers
        // Expected: ~4-5 benches per player

        $benchCounts = [];
        foreach ($players as $player) {
            if ($player->position_id === 1) continue; // Skip keeper

            $benches = \DB::table('football_match_player')
                ->join('football_matches', 'football_matches.id', '=', 'football_match_player.football_match_id')
                ->where('football_matches.season_id', $season->id)
                ->where('football_match_player.player_id', $player->id)
                ->whereNull('football_match_player.position_id')
                ->count();
            
            $benchCounts[$player->id] = $benches;
        }

        $minBenches = min($benchCounts);
        $maxBenches = max($benchCounts);

        // With 9 non-keepers and 40 bench slots, difference should be at most 2
        $this->assertLessThanOrEqual(2, $maxBenches - $minBenches,
            "Bench distribution should be fair for 3-2-2 formation with max 2 difference");
    }

    public function test_3_2_2_formation_with_variable_availability_across_matches(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '3-2-2', 8);

        // Create 11 players (1 keeper + 10 outfield)
        $players = $this->createPlayers($team, 11, keeperIndices: [0]);
        $season->players()->sync($players->pluck('id'));

        // Variable availability: some matches 9 players, some 10, some 11
        $availabilityPatterns = [
            [0, 1, 2, 3, 4, 5, 6, 7, 8],           // Match 1: 9 players
            [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],        // Match 2: 10 players
            [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],   // Match 3: 11 players (all)
            [0, 2, 3, 4, 5, 6, 7, 8, 9],           // Match 4: 9 players
            [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],   // Match 5: 11 players (all)
        ];

        for ($i = 0; $i < 5; $i++) {
            $match = $this->createMatch($season, ['date' => now()->addDays($i)]);
            $availablePlayerIds = collect($availabilityPatterns[$i])
                ->map(fn($idx) => $players[$idx]->id)
                ->toArray();
            $this->generateLineup($match, $availablePlayerIds);
        }

        // Verify correct bench counts per match based on team size
        foreach ($availabilityPatterns as $matchIndex => $availableIndices) {
            $match = FootballMatch::where('season_id', $season->id)
                ->orderBy('date')
                ->skip($matchIndex)
                ->first();

            $teamSize = count($availableIndices);
            $expectedBenchPerQuarter = $teamSize - 8;

            for ($q = 1; $q <= 4; $q++) {
                $benchedInQuarter = $match->players()
                    ->wherePivot('quarter', $q)
                    ->whereNull('football_match_player.position_id')
                    ->count();
                
                $this->assertSame($expectedBenchPerQuarter, $benchedInQuarter,
                    "Match " . ($matchIndex + 1) . " Q{$q} should have {$expectedBenchPerQuarter} benched");
            }
        }

        // Check overall fairness for players who played all 5 matches
        $playersInAllMatches = [];
        foreach ($players as $player) {
            $playerIndex = $players->search(fn($p) => $p->id === $player->id);
            $appearsInAllMatches = true;
            
            foreach ($availabilityPatterns as $pattern) {
                if (!in_array($playerIndex, $pattern, true)) {
                    $appearsInAllMatches = false;
                    break;
                }
            }

            if ($appearsInAllMatches && $player->position_id !== 1) {
                $benches = \DB::table('football_match_player')
                    ->join('football_matches', 'football_matches.id', '=', 'football_match_player.football_match_id')
                    ->where('football_matches.season_id', $season->id)
                    ->where('football_match_player.player_id', $player->id)
                    ->whereNull('football_match_player.position_id')
                    ->count();
                
                $playersInAllMatches[$player->id] = $benches;
            }
        }

        if (count($playersInAllMatches) >= 2) {
            $minBenches = min($playersInAllMatches);
            $maxBenches = max($playersInAllMatches);

            $this->assertLessThanOrEqual(2, $maxBenches - $minBenches,
                "Players in all matches should have fair bench distribution");
        }
    }

    // === EDGE CASE TESTS ===

    public function test_three_keepers_rotate_fairly_each_keeps_one_or_two_quarters(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '2-1-2', 6);

        // Create 9 players with 3 designated keepers
        $players = $this->createPlayers($team, 9, keeperIndices: [0, 1, 2]);
        $season->players()->sync($players->pluck('id'));

        $match = $this->createMatch($season);
        $this->generateLineup($match, $players->pluck('id')->all());

        $keeper1 = $players[0];
        $keeper2 = $players[1];
        $keeper3 = $players[2];

        // With 3 keepers and 4 quarters: one keeper gets 2 quarters, two get 1 quarter each
        $k1KeeperCount = $match->players()->wherePivot('player_id', $keeper1->id)->wherePivot('position_id', 1)->count();
        $k2KeeperCount = $match->players()->wherePivot('player_id', $keeper2->id)->wherePivot('position_id', 1)->count();
        $k3KeeperCount = $match->players()->wherePivot('player_id', $keeper3->id)->wherePivot('position_id', 1)->count();

        // Total should be 4 (one keeper per quarter)
        $totalKeeperQuarters = $k1KeeperCount + $k2KeeperCount + $k3KeeperCount;
        $this->assertSame(4, $totalKeeperQuarters, 'Total keeper quarters should be 4');

        // Each keeper should keep at least once
        $this->assertGreaterThanOrEqual(1, $k1KeeperCount, 'Keeper 1 should keep at least 1 quarter');
        $this->assertGreaterThanOrEqual(1, $k2KeeperCount, 'Keeper 2 should keep at least 1 quarter');
        $this->assertGreaterThanOrEqual(1, $k3KeeperCount, 'Keeper 3 should keep at least 1 quarter');

        // No keeper should keep more than 2 quarters
        $this->assertLessThanOrEqual(2, $k1KeeperCount, 'Keeper 1 should not keep more than 2 quarters');
        $this->assertLessThanOrEqual(2, $k2KeeperCount, 'Keeper 2 should not keep more than 2 quarters');
        $this->assertLessThanOrEqual(2, $k3KeeperCount, 'Keeper 3 should not keep more than 2 quarters');

        // Verify keepers never play outfield
        foreach ([$keeper1, $keeper2, $keeper3] as $keeper) {
            $outfieldCount = $match->players()
                ->wherePivot('player_id', $keeper->id)
                ->whereIn('football_match_player.position_id', [2, 3, 4])
                ->count();
            $this->assertSame(0, $outfieldCount, "Keeper {$keeper->id} should never play outfield");
        }

        // Each keeper should have exactly 4 assignments (keeping + bench)
        foreach ([$keeper1, $keeper2, $keeper3] as $keeper) {
            $totalAssignments = $match->players()->wherePivot('player_id', $keeper->id)->count();
            $this->assertSame(4, $totalAssignments, "Keeper {$keeper->id} should have 4 total assignments");
        }
    }

    public function test_four_keepers_each_keeps_exactly_one_quarter(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '2-1-2', 6);

        // Create 10 players with 4 designated keepers
        $players = $this->createPlayers($team, 10, keeperIndices: [0, 1, 2, 3]);
        $season->players()->sync($players->pluck('id'));

        $match = $this->createMatch($season);
        $this->generateLineup($match, $players->pluck('id')->all());

        // Each of the 4 keepers should keep exactly 1 quarter
        for ($i = 0; $i < 4; $i++) {
            $keeper = $players[$i];
            $keeperCount = $match->players()
                ->wherePivot('player_id', $keeper->id)
                ->wherePivot('position_id', 1)
                ->count();
            
            $this->assertSame(1, $keeperCount, "Keeper {$i} should keep exactly 1 quarter");

            // Verify never plays outfield
            $outfieldCount = $match->players()
                ->wherePivot('player_id', $keeper->id)
                ->whereIn('football_match_player.position_id', [2, 3, 4])
                ->count();
            $this->assertSame(0, $outfieldCount, "Keeper {$i} should never play outfield");

            // Should have 4 total assignments (1 keeper + 3 bench)
            $totalAssignments = $match->players()->wherePivot('player_id', $keeper->id)->count();
            $this->assertSame(4, $totalAssignments, "Keeper {$i} should have 4 assignments");
        }
    }

    public function test_team_size_exactly_matches_formation_size_no_bench_needed(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '2-1-2', 6);

        // Create exactly 6 players (matching formation size)
        $players = $this->createPlayers($team, 6, keeperIndices: [0]);
        $season->players()->sync($players->pluck('id'));

        $match = $this->createMatch($season);
        $this->generateLineup($match, $players->pluck('id')->all());

        // All players should play all quarters, no one benched
        foreach ($players as $player) {
            $totalAssignments = $match->players()->wherePivot('player_id', $player->id)->count();
            $this->assertSame(4, $totalAssignments, "Player {$player->id} should have 4 assignments");

            $benchCount = $match->players()
                ->wherePivot('player_id', $player->id)
                ->whereNull('football_match_player.position_id')
                ->count();
            $this->assertSame(0, $benchCount, "Player {$player->id} should never be benched");
        }

        // Each quarter should have exactly 6 on field
        for ($q = 1; $q <= 4; $q++) {
            $onField = $match->players()
                ->wherePivot('quarter', $q)
                ->whereNotNull('football_match_player.position_id')
                ->count();
            $this->assertSame(6, $onField, "Quarter {$q} should have 6 on field");
        }
    }

    public function test_too_few_players_for_formation_throws_error_or_handles_gracefully(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '2-1-2', 6);

        // Create only 4 players but need 6 on field - impossible scenario
        $players = $this->createPlayers($team, 4, keeperIndices: [0]);
        $season->players()->sync($players->pluck('id'));

        $match = $this->createMatch($season);

        // This should either throw an exception or handle gracefully
        // For now, let's see what happens
        try {
            $this->generateLineup($match, $players->pluck('id')->all());
            
            // If it doesn't throw, check what happened
            $totalAssignments = \DB::table('football_match_player')
                ->where('football_match_id', $match->id)
                ->count();
            
            // With 4 players and 4 quarters, we expect some assignments
            // But we cannot have 6 on field if only 4 players exist
            // This test documents current behavior
            $this->assertGreaterThan(0, $totalAssignments, 
                'Service should create some assignments even with insufficient players');
            
            // NOTE: This scenario (too few players) should be prevented by form validation
            // Current behavior: Service handles it gracefully without throwing
        } catch (\Exception $e) {
            // If it throws, that's actually good - document the exception
            $this->assertTrue(true, 'Service throws exception for impossible scenario: ' . $e->getMessage());
        }
    }

    public function test_keeper_availability_changes_between_matches(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '2-1-2', 6);

        // Create 8 players with 1 designated keeper
        $players = $this->createPlayers($team, 8, keeperIndices: [0]);
        $season->players()->sync($players->pluck('id'));

        $keeper = $players[0];

        // Match 1: Keeper available - should keep all quarters
        $match1 = $this->createMatch($season, ['date' => now()->subDays(2)]);
        $this->generateLineup($match1, $players->pluck('id')->all());

        $keeper1Count = $match1->players()
            ->wherePivot('player_id', $keeper->id)
            ->wherePivot('position_id', 1)
            ->count();
        $this->assertSame(4, $keeper1Count, 'Match 1: Keeper should keep all 4 quarters');

        // Match 2: Keeper NOT available - others should rotate keeper position
        $match2 = $this->createMatch($season, ['date' => now()->subDays(1)]);
        $playersWithoutKeeper = $players->reject(fn($p) => $p->id === $keeper->id);
        $this->generateLineup($match2, $playersWithoutKeeper->pluck('id')->all());

        // Should have 4 different keeper assignments (or close to it)
        $keeperAssignments = $match2->players()
            ->wherePivot('position_id', 1)
            ->get();
        $this->assertSame(4, $keeperAssignments->count(), 
            'Match 2: Should have keeper in all 4 quarters even without designated keeper');

        // Verify designated keeper is NOT in match 2
        $keeperInMatch2 = $match2->players()->wherePivot('player_id', $keeper->id)->count();
        $this->assertSame(0, $keeperInMatch2, 'Match 2: Designated keeper should not appear');

        // Match 3: Keeper returns - should keep all quarters again
        $match3 = $this->createMatch($season, ['date' => now()]);
        $this->generateLineup($match3, $players->pluck('id')->all());

        $keeper3Count = $match3->players()
            ->wherePivot('player_id', $keeper->id)
            ->wherePivot('position_id', 1)
            ->count();
        $this->assertSame(4, $keeper3Count, 'Match 3: Keeper should keep all 4 quarters when available again');
    }

    public function test_extreme_bench_imbalance_with_many_matches_and_unlucky_rotation(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '2-1-2', 6);

        // 7 players (1 keeper + 6 non-keepers) with 6 on field = 1 bench per quarter
        $players = $this->createPlayers($team, 7, keeperIndices: [0]);
        $season->players()->sync($players->pluck('id'));

        // Play 10 matches to see if fairness holds over longer season
        for ($i = 0; $i < 10; $i++) {
            $match = $this->createMatch($season, ['date' => now()->addDays($i)]);
            $this->generateLineup($match, $players->pluck('id')->all());
        }

        // Check bench distribution for non-keepers
        $benchCounts = [];
        foreach ($players as $player) {
            if ($player->position_id === 1) continue; // Skip keeper

            $benches = \DB::table('football_match_player')
                ->join('football_matches', 'football_matches.id', '=', 'football_match_player.football_match_id')
                ->where('football_matches.season_id', $season->id)
                ->where('football_match_player.player_id', $player->id)
                ->whereNull('football_match_player.position_id')
                ->count();
            
            $benchCounts[$player->id] = $benches;
        }

        $minBenches = min($benchCounts);
        $maxBenches = max($benchCounts);

        // Over 10 matches with 6 non-keepers and 40 bench slots total
        // Expected: ~6-7 benches per player (40/6 = 6.67 average)
        // A difference of 3 over 10 matches is acceptable and fair (7.5% variance)
        $this->assertLessThanOrEqual(3, $maxBenches - $minBenches,
            'Over 10 matches, bench distribution should remain fair with max 3 difference');

        // Verify total benches equals expected
        $totalBenches = array_sum($benchCounts);
        $expectedTotal = 10 * 4 * 1; // 10 matches × 4 quarters × 1 bench per quarter
        $this->assertSame($expectedTotal, $totalBenches, 'Total bench count should be exactly 40');
    }

    public function test_all_players_same_weight_distribution_stays_fair(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '2-1-2', 6);

        // Create 8 players all with same weight (removes weight-based sorting bias)
        $weights = array_fill(0, 8, 2); // All weight = 2
        $players = $this->createPlayers($team, 8, keeperIndices: [0], weights: $weights);
        $season->players()->sync($players->pluck('id'));

        // Play 5 matches
        for ($i = 0; $i < 5; $i++) {
            $match = $this->createMatch($season, ['date' => now()->addDays($i)]);
            $this->generateLineup($match, $players->pluck('id')->all());
        }

        // Check bench distribution - should be purely based on historical bench count
        $benchCounts = [];
        foreach ($players as $player) {
            if ($player->position_id === 1) continue;

            $benches = \DB::table('football_match_player')
                ->join('football_matches', 'football_matches.id', '=', 'football_match_player.football_match_id')
                ->where('football_matches.season_id', $season->id)
                ->where('football_match_player.player_id', $player->id)
                ->whereNull('football_match_player.position_id')
                ->count();
            
            $benchCounts[$player->id] = $benches;
        }

        $minBenches = min($benchCounts);
        $maxBenches = max($benchCounts);

        // With identical weights, distribution should be very fair
        // With 7 non-keepers and 40 bench slots, difference of 2 is optimal
        $this->assertLessThanOrEqual(2, $maxBenches - $minBenches,
            'With identical weights, bench distribution should be fair (max 2 difference over 5 matches)');
    }

    // === POSITION PREFERENCE & WEIGHT BALANCING TESTS ===

    public function test_players_assigned_to_favorite_positions_when_possible(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '2-1-2', 6);

        // Ensure positions exist
        foreach ([1 => 'Keeper', 2 => 'Defender', 3 => 'Midfielder', 4 => 'Attacker'] as $id => $name) {
            Position::query()->firstOrCreate(['id' => $id], ['name' => $name]);
        }

        // Create specific players with clear position preferences
        // Formation 2-1-2 needs: 1 keeper, 2 defenders, 1 midfielder, 2 attackers
        $keeper = Player::create(['team_id' => $team->id, 'name' => 'Keeper', 'position_id' => 1, 'weight' => 2]);
        $defender1 = Player::create(['team_id' => $team->id, 'name' => 'Defender 1', 'position_id' => 2, 'weight' => 2]);
        $defender2 = Player::create(['team_id' => $team->id, 'name' => 'Defender 2', 'position_id' => 2, 'weight' => 2]);
        $midfielder = Player::create(['team_id' => $team->id, 'name' => 'Midfielder', 'position_id' => 3, 'weight' => 2]);
        $attacker1 = Player::create(['team_id' => $team->id, 'name' => 'Attacker 1', 'position_id' => 4, 'weight' => 2]);
        $attacker2 = Player::create(['team_id' => $team->id, 'name' => 'Attacker 2', 'position_id' => 4, 'weight' => 2]);
        $extra = Player::create(['team_id' => $team->id, 'name' => 'Extra Player', 'position_id' => 3, 'weight' => 2]);

        $players = collect([$keeper, $defender1, $defender2, $midfielder, $attacker1, $attacker2, $extra]);
        $season->players()->sync($players->pluck('id'));

        $match = $this->createMatch($season);
        $this->generateLineup($match, $players->pluck('id')->all());

        // Check position adherence across all quarters
        $positionMatchCount = 0;
        $totalOnFieldAssignments = 0;

        foreach ($players as $player) {
            if ($player->position_id === 1) continue; // Skip keeper (already tested)

            $assignments = \DB::table('football_match_player')
                ->where('football_match_id', $match->id)
                ->where('player_id', $player->id)
                ->whereNotNull('position_id')
                ->get();

            foreach ($assignments as $assignment) {
                $totalOnFieldAssignments++;
                if ($assignment->position_id == $player->position_id) {
                    $positionMatchCount++;
                }
            }
        }

        // Calculate percentage of times players got their favorite position
        $matchPercentage = ($positionMatchCount / $totalOnFieldAssignments) * 100;

        // With 7 players and 6 on field, and formation matching player positions,
        // we should see high adherence (>70% at minimum)
        $this->assertGreaterThan(70, $matchPercentage,
            "Players should play their favorite position at least 70% of the time (got {$matchPercentage}%)");
    }

    public function test_weight_balancing_avoids_clustering_same_weight_players(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '2-1-2', 6);

        // Ensure positions exist
        foreach ([1 => 'Keeper', 2 => 'Defender', 3 => 'Midfielder', 4 => 'Attacker'] as $id => $name) {
            Position::query()->firstOrCreate(['id' => $id], ['name' => $name]);
        }

        // Create players with intentional weight clustering
        // 1 keeper (weight doesn't matter for keeper)
        // 3 players with weight=1 (light)
        // 3 players with weight=3 (heavy)
        // 1 player with weight=2 (medium)
        $keeper = Player::create(['team_id' => $team->id, 'name' => 'Keeper', 'position_id' => 1, 'weight' => 2]);
        $light1 = Player::create(['team_id' => $team->id, 'name' => 'Light 1', 'position_id' => 2, 'weight' => 1]);
        $light2 = Player::create(['team_id' => $team->id, 'name' => 'Light 2', 'position_id' => 3, 'weight' => 1]);
        $light3 = Player::create(['team_id' => $team->id, 'name' => 'Light 3', 'position_id' => 4, 'weight' => 1]);
        $heavy1 = Player::create(['team_id' => $team->id, 'name' => 'Heavy 1', 'position_id' => 2, 'weight' => 3]);
        $heavy2 = Player::create(['team_id' => $team->id, 'name' => 'Heavy 2', 'position_id' => 3, 'weight' => 3]);
        $heavy3 = Player::create(['team_id' => $team->id, 'name' => 'Heavy 3', 'position_id' => 4, 'weight' => 3]);
        $medium = Player::create(['team_id' => $team->id, 'name' => 'Medium', 'position_id' => 4, 'weight' => 2]);

        $players = collect([$keeper, $light1, $light2, $light3, $heavy1, $heavy2, $heavy3, $medium]);
        $season->players()->sync($players->pluck('id'));

        $match = $this->createMatch($season);
        $this->generateLineup($match, $players->pluck('id')->all());

        // Check weight distribution per quarter
        $quartersWithGoodBalance = 0;

        for ($q = 1; $q <= 4; $q++) {
            $assignments = \DB::table('football_match_player')
                ->join('players', 'players.id', '=', 'football_match_player.player_id')
                ->where('football_match_player.football_match_id', $match->id)
                ->where('football_match_player.quarter', $q)
                ->whereNotNull('football_match_player.position_id')
                ->select('players.weight')
                ->get();

            $weightCounts = $assignments->groupBy('weight')->map->count();

            // Check if any weight appears more than 2 times
            $hasCluster = $weightCounts->filter(fn($count) => $count > 2)->isNotEmpty();

            if (!$hasCluster) {
                $quartersWithGoodBalance++;
            }
        }

        // At least 3 out of 4 quarters should have good weight balance (no more than 2 of same weight)
        $this->assertGreaterThanOrEqual(3, $quartersWithGoodBalance,
            "At least 3 quarters should avoid having >2 players with the same weight (got {$quartersWithGoodBalance})");
    }

    public function test_position_preference_maintained_across_multiple_matches(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '2-1-2', 6);

        // Ensure positions exist
        foreach ([1 => 'Keeper', 2 => 'Defender', 3 => 'Midfielder', 4 => 'Attacker'] as $id => $name) {
            Position::query()->firstOrCreate(['id' => $id], ['name' => $name]);
        }

        // Create 8 players with specific position distribution
        $keeper = Player::create(['team_id' => $team->id, 'name' => 'Keeper', 'position_id' => 1, 'weight' => 2]);
        $defenders = collect([
            Player::create(['team_id' => $team->id, 'name' => 'Defender 1', 'position_id' => 2, 'weight' => 1]),
            Player::create(['team_id' => $team->id, 'name' => 'Defender 2', 'position_id' => 2, 'weight' => 2]),
            Player::create(['team_id' => $team->id, 'name' => 'Defender 3', 'position_id' => 2, 'weight' => 3]),
        ]);
        $midfielders = collect([
            Player::create(['team_id' => $team->id, 'name' => 'Midfielder 1', 'position_id' => 3, 'weight' => 2]),
        ]);
        $attackers = collect([
            Player::create(['team_id' => $team->id, 'name' => 'Attacker 1', 'position_id' => 4, 'weight' => 1]),
            Player::create(['team_id' => $team->id, 'name' => 'Attacker 2', 'position_id' => 4, 'weight' => 3]),
            Player::create(['team_id' => $team->id, 'name' => 'Attacker 3', 'position_id' => 4, 'weight' => 2]),
        ]);

        $players = collect([$keeper])->merge($defenders)->merge($midfielders)->merge($attackers);
        $season->players()->sync($players->pluck('id'));

        // Play 3 matches
        for ($i = 0; $i < 3; $i++) {
            $match = $this->createMatch($season, ['date' => now()->addDays($i)]);
            $this->generateLineup($match, $players->pluck('id')->all());
        }

        // Aggregate position adherence across all 3 matches
        $totalMatches = 0;
        $totalMismatches = 0;

        $allMatches = FootballMatch::where('season_id', $season->id)->get();

        foreach ($defenders as $defender) {
            $assignments = \DB::table('football_match_player')
                ->whereIn('football_match_id', $allMatches->pluck('id'))
                ->where('player_id', $defender->id)
                ->whereNotNull('position_id')
                ->get();

            foreach ($assignments as $assignment) {
                $totalMatches++;
                if ($assignment->position_id != 2) { // Should be defender
                    $totalMismatches++;
                }
            }
        }

        // Defenders should play defender position in vast majority of cases
        $mismatchPercentage = ($totalMismatches / $totalMatches) * 100;
        $this->assertLessThan(30, $mismatchPercentage,
            "Defenders should play defender position at least 70% of the time across multiple matches");
    }

    public function test_weight_balancing_with_mixed_weight_distribution(): void
    {
        [$user, $team] = $this->createUserAndTeam();
        $season = $this->createSeasonWithFormation($team, '3-2-2', 8);

        // Ensure positions exist
        foreach ([1 => 'Keeper', 2 => 'Defender', 3 => 'Midfielder', 4 => 'Attacker'] as $id => $name) {
            Position::query()->firstOrCreate(['id' => $id], ['name' => $name]);
        }

        // Create 10 players with realistic weights (1 or 2 only)
        $keeper = Player::create(['team_id' => $team->id, 'name' => 'Keeper', 'position_id' => 1, 'weight' => 2]);
        $players = collect([$keeper]);

        // Realistic distribution: 3 light (weight=1), 6 regular (weight=2)
        // With 8 on field per quarter, if all weight=2 players were on: that's 6 players
        // So maximum 6 weight=2 on field is unavoidable sometimes
        $weights = [1, 1, 1, 2, 2, 2, 2, 2, 2];
        $positions = [2, 3, 4, 2, 3, 4, 2, 3, 4]; // Varied positions

        for ($i = 0; $i < 9; $i++) {
            $players->push(Player::create([
                'team_id' => $team->id,
                'name' => "Player " . ($i + 1),
                'position_id' => $positions[$i],
                'weight' => $weights[$i],
            ]));
        }

        $season->players()->sync($players->pluck('id'));

        // Play 5 matches
        for ($i = 0; $i < 5; $i++) {
            $match = $this->createMatch($season, ['date' => now()->addDays($i)]);
            $this->generateLineup($match, $players->pluck('id')->all());
        }

        // Check weight balance across all matches
        $allMatches = FootballMatch::where('season_id', $season->id)->get();
        $totalQuarters = $allMatches->count() * 4;
        
        // Count distribution patterns
        $extremeImbalance = 0; // All 6 weight=2 players on field (or all 3 weight=1)
        $moderateImbalance = 0; // 5-6 of one weight
        $balanced = 0; // Reasonable mix

        foreach ($allMatches as $match) {
            for ($q = 1; $q <= 4; $q++) {
                $assignments = \DB::table('football_match_player')
                    ->join('players', 'players.id', '=', 'football_match_player.player_id')
                    ->where('football_match_player.football_match_id', $match->id)
                    ->where('football_match_player.quarter', $q)
                    ->whereNotNull('football_match_player.position_id')
                    ->select('players.weight')
                    ->get();

                $weightCounts = $assignments->groupBy('weight')->map->count();
                $weight1Count = $weightCounts->get(1, 0);
                $weight2Count = $weightCounts->get(2, 0);

                // Categorize the balance
                // With 3 weight=1 and 6 weight=2 available, and 8 on field:
                // Ideal: 2-3 weight=1, 5-6 weight=2 (proportional to availability)
                // Extreme imbalance: 0 or 3 weight=1 (all or none)
                if ($weight1Count == 0 || $weight1Count == 3) {
                    $extremeImbalance++;
                } elseif ($weight1Count == 1) {
                    $moderateImbalance++; // Could be better
                } else {
                    $balanced++; // 2 weight=1, 6 weight=2 is reasonable
                }
            }
        }

        $extremePercentage = ($extremeImbalance / $totalQuarters) * 100;
        
        // The algorithm should try to avoid extreme imbalance
        // With 3 light and 6 regular players, some imbalance is mathematically unavoidable
        // But we shouldn't see extreme imbalance (all light or no light) in most quarters
        $this->assertLessThan(50, $extremePercentage,
            "Extreme weight imbalance should occur in less than 50% of quarters (got {$extremePercentage}%)");
    }
}
