# jeugdvoetbalcoach.nl - AI Coding Instructions

A Laravel 12 SaaS application for youth football coaches (JO8-JO12) to manage teams, generate intelligent lineups for 4-quarter matches, and ensure fair player rotation.

## Architecture Overview

**Multi-tenant Team System**: Users belong to multiple teams via `team_user` pivot (roles: hoofdcoach=1, assistent=2). Session stores `current_team_id` for context. Teams link to `opponents` table for club info (globally shared). Authorization via `$user->isMemberOf($team)` (defined in `User` model), never filter by direct `user_id`. Use `auth()->user()->teams()` for queries.

**Data Isolation Pattern**: All resources (players, seasons, matches, formations) belong to teams via foreign keys. Policies enforce team membership for CRUD operations - check `$user->isMemberOf($resource->team)` (see `PlayerPolicy::view()` for pattern). Controllers load team-scoped data: `$team->players()`, `$team->seasons()`, never `Player::all()`.

**Position IDs** (fixed, global, seeded): 1=Keeper, 2=Defender, 3=Midfielder, 4=Attacker. Referenced as constants in `LineupGeneratorService` (e.g., `KEEPER_POSITION_ID = 1`). Never hardcode IDs outside service constants.

## Core Domain Logic

### LineupGeneratorService - Critical Business Rules

Located at `app/Services/LineupGeneratorService.php` with support classes in `app/Services/LineupGenerator/` (`QuarterAssignmentData`, `AssignmentResult`). See `docs/LineupGeneratorService.md` for full documentation. 22 unit tests in `tests/Unit/LineupGeneratorServiceTest.php`.

**Formation System**: Dynamically loaded from `Season->formation` relationship (globally shared or team-specific). Parse `lineup_formation` string (e.g., "2-1-2") via `parseFormationNeeds()` to get outfield role counts `['defender' => 2, 'midfielder' => 1, 'attacker' => 2]`. Desired on-field: `getDesiredOnField()` computes `formation.total_players > 0 ? total_players : 1 + sum(parsed_lineup)` (1 keeper + outfield). NEVER hardcode on-field counts. Formation validated via `ValidFormation` rule - `lineup_formation` sum must equal `total_players - 1`.

**Keeper Logic** (context-sensitive, see `selectKeepers()` and related methods):
- **0 keepers**: All players eligible for keeper position, 4 selected randomly with weight balance, each gets 1 bench quarter (not in keeper quarter)
- **1 keeper**: Plays all 4 quarters, NEVER benched (critical constraint, tested)
- **2 keepers**: Each keeps 2 quarters, benches 2 quarters (non-keeper quarters only), NEVER plays outfield
- **3-4 keepers**: Each keeps 1-2 quarters evenly distributed, benched when not keeping (tested for fairness)
- **Historical priority**: Avoids keepers from last match (`getLastMatchKeepers()`), prioritizes players with lowest `keeper_count`

**Bench Distribution Algorithm** (see `createBenchPlan()` and `distributeNonKeeperBenches()`):
1. Load bench counts from last 3 matches via `getRecentBenchCounts()` (DB query on `football_match_player` where `position_id IS NULL`)
2. Sort players by bench count ascending (least benched first) for fairness priority
3. Calculate bench need per quarter: `teamSize - desiredOnField`
4. **Keepers**: Exactly 1 bench quarter each in non-keeper quarters
5. **Non-keepers**: Fill remaining bench slots evenly across quarters via `pickQuarterForPlayer()` (avoid adjacent quarters where possible)
6. **New players**: 0 bench history = automatic priority (tested for mid-season joins)

**Weight Balancing**: Penalizes >2 players with weight=1 or weight=2 in same quarter. Sorting via `sortByBalanceAndWeight()` before assignment. Tolerance <50% extreme clustering (realistic constraint, tested with 3 light/6 regular distribution).

**Position Assignment**: Players assigned to their `position_id` preference >70% of time (tested). Fallback system fills with suitable alternatives when exact match unavailable. Map roles via `ROLE_POSITION_MAP`.

**Immutability Pattern**: All operations return new instances (no pass-by-reference). `QuarterAssignmentData` is immutable state holder with fluent methods: `withBenchedPlayers()`, `withAssignedKeeper()`, `withAssignedPlayer()`. Never mutate state in-place.

## Database Patterns

See `docs/database-schema.md` for full ERD and schema details.

**Pivot Tables**: 
- `team_user` (user_id, team_id, role, is_default, joined_at, label) - Maps users to teams with roles (hoofdcoach=1, assistent=2). `is_default` marks user's default team. `label` stores custom role description.
- `football_match_player` (player_id, football_match_id, quarter, position_id) - Lineup data. `position_id` NULL = benched. Quarter 1-4.
- `player_season` (player_id, season_id) - Many-to-many for players in multiple seasons.

**Share Tokens**: Football matches and seasons have `share_token` (string, nullable, unique, 32-64 chars) for public access via routes like `/football-matches/{match}/share/{shareToken}` and `/seasons/{season}/share/{shareToken}`. Regenerate with `Str::random(64)`. Controllers use `throttle:30,1` middleware. No auth required.

**Invite Codes**: Teams have `invite_code` (string, nullable, unique, 64 chars) for join flow. Routes: `GET /teams/join/{inviteCode}` (public show, throttled), `POST /teams/join/{inviteCode}` (requires auth). Regenerate via `TeamController::regenerateInviteCode()`.

## Development Workflow

**Commands** (defined in `composer.json` scripts):
- `composer dev` - Runs concurrent server, queue worker, pail logs, and Vite dev server with colored output (uses `npx concurrently`)
- `composer test` or `php artisan test` - Pest PHP test suite (runs config:clear first)
- `php artisan migrate:fresh --seed` - Reset database with seeders (positions, global formations, sample opponents/players)
- `php artisan clubs:fetch` - Scrapes Dutch football clubs from CSV + Hollandse Velden website, stores in `opponents` table with logos

**Seeded Data** (`database/seeders/`): 
- Positions table: IDs 1-4 (Keeper, Defender, Midfielder, Attacker) via `PositionSeeder`
- 3 global formations: 6p/2-1-2, 8p/3-2-2, 11p/4-3-3 via `FormationSeeder` (team_id NULL = global)
- Sample opponents via `OpponentSeeder`
- Sample teams, players, seasons, matches via `DatabaseSeeder`

**Frontend Stack**: Blade templates + Tailwind CSS 4 via Vite (no JS framework). Use `@can('action', $model)` Blade directives for inline authorization checks. Components in `resources/views/components/`. Build assets with `npm run build` for production.

## Authorization & Policies

**Role Hierarchy**: Admin (`role=1`, `User::isAdmin()`) has full access. Regular users (`role=2`) see only their teams' data. Inactive users (`is_active=false`) blocked at policy level.

**Policy Pattern**: All controllers use `Gate::authorize('action', $resource)` before operations. Policies (in `app/Policies/`) check `$user->isMemberOf($resource->team)` via Eloquent relationships. Example pattern from `PlayerPolicy::view()`:
```php
return $user->is_active && $user->isMemberOf($player->team);
```
Models have `$user->teams()` relationship for multi-team access. Never check direct `user_id` on resources.

**Admin Routes**: Wrapped in `middleware(['admin'])` at `/admin/*` prefix - see `routes/web.php`. Admin can manage:
- Positions (global resource)
- Users (role/active status via `Admin\UserController`)
- Global formations (`team_id` NULL)
- Opponents (globally shared clubs)

**Team-Level Permissions** (enforced in `TeamPolicy`):
- **Delete team**: Only hoofdcoach (role=1), and only if ≥1 assistent exists (prevent orphaned teams)
- **Leave team**: Assistenten always allowed; hoofdcoach only if ≥1 assistent remains to take over
- **Update team**: Any team member
- **Regenerate invite code**: Any team member

## Common Patterns

**Team Context Management**: Session-based team switching for multi-team users. Controllers scope data via `session('current_team_id')`:
- Read: `$team = Team::find(session('current_team_id'))`
- Set: `session(['current_team_id' => $team->id])` in `TeamController::switch()`
- Default team: `team_user.is_default` column marks user's preferred team (set on first team join or via `setDefault()`)
- Assignment: `$validated['team_id'] = session('current_team_id')` when creating team-owned resources

**Formation Validation**: Custom `ValidFormation` rule validates formation strings. Usage pattern:
```php
new ValidFormation($totalPlayers)  // Pass total_players to validate sum
```
Validates `lineup_formation` (e.g., "2-1-2") sum equals `total_players - 1` (excluding keeper). Rejects non-numeric or negative values.

**Opponent Integration**: Teams link to single `opponent_id` for club info (name, logo, maps_location). Shared globally across all teams, populated via `php artisan clubs:fetch` scraper. Provides autocomplete endpoint at `/api/opponents` (throttled, public).

**Match Goals Tracking**: `match_goals` table stores per-goal data: `player_id` (scorer), `assist_player_id` (nullable), `minute`, `subtype`. Many-to-one with players and matches. Optional feature enabled via `Season->track_goals` boolean.

## Testing Conventions

Tests use Pest PHP (`tests/Feature/`, `tests/Unit/`). Auth tests from Laravel Breeze. Key test patterns:
- **Service tests**: `LineupGeneratorServiceTest` has 22 unit tests covering keeper logic, bench fairness, weight balance, variable availability
- **Test helpers**: Use `$this->withSession(['current_team_id' => $team->id])` to simulate team context
- Write feature tests for policies (authorization paths) and critical service logic
- Coverage gaps: Most controllers lack feature tests - good opportunity to add request/response validation tests

## Dutch Language

UI, variable names, and comments mix Dutch (team names, UI labels) and English (code, comments). Maintain consistency: Dutch for domain terms (hoofdcoach, assistent, tegenstander), English for code constructs.

## JavaScript

Always write JavaScript in de app.js file, never inline in the blade templates
