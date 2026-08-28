<?php

use App\Models\Formation;
use App\Models\FootballMatch;
use App\Models\Opponent;
use App\Models\Player;
use App\Models\Position;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Str;

// ─── Season: default_substitutions_per_quarter ───────────────────────────────

describe('Season default_substitutions_per_quarter', function () {
    beforeEach(function () {
        $this->user = User::factory()->create(['is_active' => true]);
        $this->team = Team::create([
            'name' => 'Feature Test Team',
            'opponent_id' => null,
            'invite_code' => Str::random(8),
        ]);
        $this->team->users()->attach($this->user->id, [
            'role' => 1, 'is_default' => 1, 'joined_at' => now(),
        ]);
        $this->formation = Formation::create([
            'team_id' => null,
            'lineup_formation' => '2-1-2',
            'total_players' => 6,
            'is_global' => true,
        ]);
    });

    test('store persists custom default_substitutions_per_quarter', function () {
        $response = $this
            ->actingAs($this->user)
            ->withSession(['current_team_id' => $this->team->id])
            ->post(route('seasons.store'), [
                'year' => now()->year,
                'part' => 1,
                'start' => now()->startOfYear()->toDateString(),
                'end' => now()->endOfYear()->toDateString(),
                'formation_id' => $this->formation->id,
                'default_substitutions_per_quarter' => 2,
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('seasons', [
            'team_id' => $this->team->id,
            'default_substitutions_per_quarter' => 2,
        ]);
    });

    test('update persists changed default_substitutions_per_quarter', function () {
        $season = Season::create([
            'team_id' => $this->team->id,
            'formation_id' => $this->formation->id,
            'year' => now()->year,
            'part' => 1,
            'start' => now()->startOfYear()->toDateString(),
            'end' => now()->endOfYear()->toDateString(),
            'track_goals' => false,
            'default_substitutions_per_quarter' => 1,
            'share_token' => Str::random(32),
        ]);

        $response = $this
            ->actingAs($this->user)
            ->withSession(['current_team_id' => $this->team->id])
            ->put(route('seasons.update', $season), [
                'year' => now()->year,
                'part' => 1,
                'start' => now()->startOfYear()->toDateString(),
                'end' => now()->endOfYear()->toDateString(),
                'formation_id' => $this->formation->id,
                'default_substitutions_per_quarter' => 3,
                'track_goals' => 0,
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('seasons', [
            'id' => $season->id,
            'default_substitutions_per_quarter' => 3,
        ]);
    });

    test('store rejects default_substitutions_per_quarter below minimum', function () {
        $this
            ->actingAs($this->user)
            ->withSession(['current_team_id' => $this->team->id])
            ->post(route('seasons.store'), [
                'year' => now()->year,
                'part' => 1,
                'start' => now()->startOfYear()->toDateString(),
                'end' => now()->endOfYear()->toDateString(),
                'formation_id' => $this->formation->id,
                'default_substitutions_per_quarter' => 0,
            ])
            ->assertSessionHasErrors('default_substitutions_per_quarter');
    });

    test('store rejects default_substitutions_per_quarter above maximum', function () {
        $this
            ->actingAs($this->user)
            ->withSession(['current_team_id' => $this->team->id])
            ->post(route('seasons.store'), [
                'year' => now()->year,
                'part' => 1,
                'start' => now()->startOfYear()->toDateString(),
                'end' => now()->endOfYear()->toDateString(),
                'formation_id' => $this->formation->id,
                'default_substitutions_per_quarter' => 5,
            ])
            ->assertSessionHasErrors('default_substitutions_per_quarter');
    });
});

// ─── FootballMatch: substitutions_per_quarter_override + view rendering ───────

describe('FootballMatch substitutions_per_quarter_override', function () {
    beforeEach(function () {
        foreach ([1 => 'Keeper', 2 => 'Defender', 3 => 'Midfielder', 4 => 'Attacker'] as $id => $name) {
            Position::query()->firstOrCreate(['id' => $id], ['name' => $name]);
        }

        $this->user = User::factory()->create(['is_active' => true]);
        $this->opponent = Opponent::create([
            'name' => 'Test Club',
            'location' => 'Amsterdam',
            'latitude' => 52.370216,
            'longitude' => 4.895168,
        ]);
        $this->team = Team::create([
            'name' => 'Feature Test Team',
            'opponent_id' => $this->opponent->id,
            'invite_code' => Str::random(8),
        ]);
        $this->team->users()->attach($this->user->id, [
            'role' => 1, 'is_default' => 1, 'joined_at' => now(),
        ]);

        $formation = Formation::create([
            'team_id' => null,
            'lineup_formation' => '2-1-2',
            'total_players' => 6,
            'is_global' => true,
        ]);
        $this->season = Season::create([
            'team_id' => $this->team->id,
            'formation_id' => $formation->id,
            'year' => now()->year,
            'part' => 1,
            'start' => now()->startOfYear()->toDateString(),
            'end' => now()->endOfYear()->toDateString(),
            'track_goals' => false,
            'default_substitutions_per_quarter' => 1,
            'share_token' => Str::random(32),
        ]);

        for ($i = 0; $i < 7; $i++) {
            $player = Player::create([
                'team_id' => $this->team->id,
                'name' => 'Player ' . ($i + 1),
                'position_id' => $i === 0 ? 1 : [2, 3, 4][$i % 3],
                'weight' => 2,
            ]);
            $this->season->players()->attach($player->id);
        }
    });

    test('store persists substitutions_per_quarter_override', function () {
        $playerIds = $this->season->players()->pluck('players.id')->toArray();

        $response = $this
            ->actingAs($this->user)
            ->withSession(['current_team_id' => $this->team->id])
            ->post(route('football-matches.store'), [
                'opponent_id' => $this->opponent->id,
                'home' => 1,
                'date' => now()->format('Y-m-d\TH:i'),
                'season_id' => $this->season->id,
                'substitutions_per_quarter_override' => 2,
                'available_players' => $playerIds,
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('football_matches', [
            'team_id' => $this->team->id,
            'substitutions_per_quarter_override' => 2,
        ]);
    });

    test('store rejects substitutions_per_quarter_override outside 1-4 range', function () {
        $playerIds = $this->season->players()->pluck('players.id')->toArray();

        $this
            ->actingAs($this->user)
            ->withSession(['current_team_id' => $this->team->id])
            ->post(route('football-matches.store'), [
                'opponent_id' => $this->opponent->id,
                'home' => 1,
                'date' => now()->format('Y-m-d\TH:i'),
                'season_id' => $this->season->id,
                'substitutions_per_quarter_override' => 5,
                'available_players' => $playerIds,
            ])
            ->assertSessionHasErrors('substitutions_per_quarter_override');
    });

    test('update persists substitutions_per_quarter_override', function () {
        $match = FootballMatch::create([
            'team_id' => $this->team->id,
            'season_id' => $this->season->id,
            'opponent_id' => $this->opponent->id,
            'home' => true,
            'date' => now(),
            'user_id' => $this->user->id,
            'substitutions_per_quarter_override' => null,
            'share_token' => Str::random(32),
        ]);

        $response = $this
            ->actingAs($this->user)
            ->withSession(['current_team_id' => $this->team->id])
            ->put(route('football-matches.update', $match), [
                'home' => 1,
                'date' => now()->format('Y-m-d\TH:i'),
                'substitutions_per_quarter_override' => 3,
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('football_matches', [
            'id' => $match->id,
            'substitutions_per_quarter_override' => 3,
        ]);
    });

    test('show page renders sub-period labels when override is 2 subs per quarter', function () {
        $match = FootballMatch::create([
            'team_id' => $this->team->id,
            'season_id' => $this->season->id,
            'opponent_id' => $this->opponent->id,
            'home' => true,
            'date' => now(),
            'user_id' => $this->user->id,
            'substitutions_per_quarter_override' => 2,
            'share_token' => Str::random(32),
        ]);

        $this
            ->actingAs($this->user)
            ->withSession(['current_team_id' => $this->team->id])
            ->get(route('football-matches.show', $match))
            ->assertOk()
            ->assertSee('Q4-2')
            ->assertDontSee('Q5-1');
    });

    test('lineup page renders sub-period tabs when override is 2 subs per quarter', function () {
        $match = FootballMatch::create([
            'team_id' => $this->team->id,
            'season_id' => $this->season->id,
            'opponent_id' => $this->opponent->id,
            'home' => true,
            'date' => now(),
            'user_id' => $this->user->id,
            'substitutions_per_quarter_override' => 2,
            'share_token' => Str::random(32),
        ]);

        $this
            ->actingAs($this->user)
            ->withSession(['current_team_id' => $this->team->id])
            ->get(route('football-matches.lineup', $match))
            ->assertOk()
            ->assertSee('Q4-2')
            ->assertDontSee('Q5-1');
    });

    test('show page defaults to 4 plain Q labels when no override is set', function () {
        $match = FootballMatch::create([
            'team_id' => $this->team->id,
            'season_id' => $this->season->id,
            'opponent_id' => $this->opponent->id,
            'home' => true,
            'date' => now(),
            'user_id' => $this->user->id,
            'substitutions_per_quarter_override' => null,
            'share_token' => Str::random(32),
        ]);

        $this
            ->actingAs($this->user)
            ->withSession(['current_team_id' => $this->team->id])
            ->get(route('football-matches.show', $match))
            ->assertOk()
            ->assertSee('Q4')
            ->assertDontSee('Q5')
            ->assertDontSee('Q1-1');
    });
});
