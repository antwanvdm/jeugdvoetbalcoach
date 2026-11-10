<?php

use App\Models\FootballMatch;
use App\Models\Formation;
use App\Models\Opponent;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;

beforeEach(function () {
    $this->travelTo(now()->setDate(2026, 11, 8)->setTime(10, 0));
    $this->user = User::factory()->create(['is_active' => true, 'role' => 2]);
    $this->opponent = Opponent::create([
        'name' => 'Testclub',
        'location' => 'Testplaats',
        'latitude' => 52,
        'longitude' => 5,
    ]);
    $this->team = Team::create(['opponent_id' => $this->opponent->id]);
    $this->team->users()->attach($this->user, ['role' => 1, 'is_default' => true, 'joined_at' => now()]);
    $formation = Formation::create([
        'lineup_formation' => '2-1-2',
        'total_players' => 6,
        'is_global' => true,
    ]);
    $this->season = Season::create([
        'team_id' => $this->team->id,
        'formation_id' => $formation->id,
        'year' => 2026,
        'part' => 1,
        'start' => '2026-01-01',
        'end' => '2026-12-31',
    ]);
    $this->makeMatch = fn (array $attributes) => FootballMatch::create(array_merge([
        'team_id' => $this->team->id,
        'season_id' => $this->season->id,
        'opponent_id' => $this->opponent->id,
        'home' => true,
    ], $attributes));
});

test('the nearest upcoming match takes priority over an outstanding result', function () {
    ($this->makeMatch)(['date' => now()->subDay()]);
    ($this->makeMatch)(['date' => now()->addDays(3)]);
    $nextMatch = ($this->makeMatch)(['date' => now()->addDay()]);

    $this->actingAs($this->user)->withSession(['current_team_id' => $this->team->id])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('nextMatch', fn ($match) => $match->is($nextMatch))
        ->assertSeeText('Volgende wedstrijd')
        ->assertSee(route('football-matches.show', $nextMatch))
        ->assertDontSeeText('Resultaat ontbreekt')
        ->assertDontSeeText('Wat is de volgende stap?');
});

test('the latest past match without a complete result links directly to the result form', function (?int $scored, ?int $conceded) {
    $olderMatch = ($this->makeMatch)(['date' => now()->subDays(2)]);
    $lastMatch = ($this->makeMatch)(['date' => now()->subDay(), 'goals_scored' => $scored, 'goals_conceded' => $conceded]);

    $this->actingAs($this->user)->withSession(['current_team_id' => $this->team->id])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('lastMatch', fn ($match) => $match->is($lastMatch))
        ->assertSeeText('Resultaat ontbreekt')
        ->assertSee(route('football-matches.edit', $lastMatch))
        ->assertDontSee(route('football-matches.edit', $olderMatch))
        ->assertDontSeeText('Wat is de volgende stap?');
})->with([[null, null], [0, null], [null, 0]]);

test('a complete goalless result offers both planning actions rather than an older missing result', function () {
    ($this->makeMatch)(['date' => now()->subDays(2)]);
    ($this->makeMatch)(['date' => now()->subDay(), 'goals_scored' => 0, 'goals_conceded' => 0]);

    $this->actingAs($this->user)->withSession(['current_team_id' => $this->team->id])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('Wat is de volgende stap?')
        ->assertSee(route('football-matches.create', ['season_id' => $this->season->id]))
        ->assertSee(route('seasons.create'))
        ->assertDontSeeText('Resultaat ontbreekt');
});

test('a team with a season but no matches can plan a first match', function () {
    $this->actingAs($this->user)->withSession(['current_team_id' => $this->team->id])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('Wat is de volgende stap?')
        ->assertSee(route('football-matches.create', ['season_id' => $this->season->id]))
        ->assertSee(route('seasons.create'));

    $this->get(route('football-matches.create', ['season_id' => $this->season->id]))
        ->assertOk()
        ->assertViewIs('football_matches.no-players');
});

test('the result action opens the edit form for the latest match', function () {
    $lastMatch = ($this->makeMatch)(['date' => now()->subDay()]);

    $this->actingAs($this->user)->withSession(['current_team_id' => $this->team->id])
        ->get(route('football-matches.edit', $lastMatch))
        ->assertOk()
        ->assertSeeText('Resultaat doorvoeren');
});

test('a team without a season can create one without a broken match planning link', function () {
    $this->season->delete();

    $this->actingAs($this->user)->withSession(['current_team_id' => $this->team->id, 'onboarding_skipped' => true])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('Wat is de volgende stap?')
        ->assertSeeText('Maak eerst een seizoen aan om wedstrijden te plannen.')
        ->assertSee(route('seasons.create'))
        ->assertDontSeeText('Plan volgende wedstrijd');
});

test('matches from another team do not influence the next action', function () {
    $otherTeam = Team::create(['opponent_id' => $this->opponent->id]);
    ($this->makeMatch)(['team_id' => $otherTeam->id, 'date' => now()->addDay()]);
    ($this->makeMatch)(['team_id' => $otherTeam->id, 'date' => now()->subDay()]);

    $this->actingAs($this->user)->withSession(['current_team_id' => $this->team->id])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('nextMatch', null)
        ->assertViewHas('lastMatch', null)
        ->assertSeeText('Wat is de volgende stap?');
});
