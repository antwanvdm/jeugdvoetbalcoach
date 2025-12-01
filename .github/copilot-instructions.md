# jeugdvoetbalcoach.nl - AI Coding Instructions

A Laravel 12 SaaS application for youth football coaches (JO8-JO12) to manage teams, generate intelligent lineups for 4-quarter matches, and ensure fair player rotation.

## Architecture Overview

**Multi-tenant Team System**: Users belong to multiple teams via `team_user` pivot (roles: hoofdcoach=1, assistent=2). Session stores `current_team_id` for context. Teams are linked to `opponents` table for club info. Use `auth()->user()->teams()` for queries, never direct user_id filters.

**Data Isolation Pattern**: All resources (players, seasons, matches, formations) belong to teams. Policies use `$user->isMemberOf($team)` for authorization. Controllers load data via team relationships: `$team->players()`, `$team->seasons()`, etc.

**Position IDs** (fixed, global): 1=Keeper, 2=Defender, 3=Midfielder, 4=Attacker (see `KEEPER_POSITION_ID` constants in `LineupGeneratorService`).

## Core Domain Logic

### LineupGeneratorService - Critical Business Rules

Located at `app/Services/LineupGeneratorService.php` with support classes in `app/Services/LineupGenerator/`.

**Formation System**: Dynamically loaded from `Season->formation` relationship. Parse `lineup_formation` (e.g., "2-1-2") via `parseFormationNeeds()` to get role distribution. `total_players` overrides calculated on-field count. Compute via `getDesiredOnField()`, never hardcode.

**Keeper Logic** (context-sensitive):
- 0 keepers: All players eligible, selected keepers get 1 bench quarter
- 1 keeper: Plays all quarters, NEVER benched
- 2+ keepers: Only assigned keeper position, get normal bench rotation

**Bench Distribution Algorithm**:
1. Calculate bench need per quarter: `teamSize - desiredOnField`
2. Keepers: exactly 1 bench quarter each (not in their keeper quarter)
3. Non-keepers: remaining bench slots distributed evenly via `distributeNonKeeperBenches()`
4. Use `pickQuarterForPlayer()` to avoid adjacent quarters where possible

**Weight Balancing**: Penalizes >2 players with same `weight` value per quarter. Sort by balance impact before assignment. See `sortByBalanceAndWeight()`.

**Immutability**: All operations return new instances (no pass-by-reference). `QuarterAssignmentData` is immutable state holder. Use `withBenchedPlayers()`, `withAssignedKeeper()` pattern.

## Database Patterns

**Pivot Tables**: `team_user` (role, is_default, joined_at, label), `football_match_player` (quarter, position_id), `player_season` (many-to-many for multi-season players).

**Share Tokens**: Football matches and seasons have `share_token` (nullable, unique) for public access via routes like `/football-matches/{match}/share/{token}`. Regenerate with `Str::random(64)`.

**Invite Codes**: Teams have `invite_code` for join flow. Routes: `/teams/join/{inviteCode}` (public show), POST requires auth.

## Development Workflow

**Commands**:
- `composer dev` - Runs concurrent server, queue, pail logs, and Vite (defined in composer.json scripts)
- `php artisan test` - Pest PHP tests
- `php artisan migrate:fresh --seed` - Reset with seeders (positions, global formations, sample data)
- `php artisan clubs:fetch` - Import opponents from Hollandse Velden API

**Seeded Data**: Positions (IDs 1-4), 3 global formations (6p/2-1-2, 8p/3-2-2, 11p/4-3-3), sample opponents and players.

**Frontend**: Blade + Tailwind CSS 4 via Vite. Use `@can('action', $model)` directives for authorization. No JavaScript framework.

## Authorization & Policies

**Role Hierarchy**: Admin (role=1) has full access. Regular users (role=2) see only their teams' data.

**Policy Pattern**: All controllers use `Gate::authorize('action', $resource)`. Policies check `$user->isMemberOf($team)` via relationships. Example: `PlayerPolicy` validates `$player->team->users->contains($user)`.

**Admin Routes**: Wrapped in `middleware(['admin'])` - see `routes/web.php` for positions, users, global formations management.

**Team Permissions**:
- Delete team: only hoofdcoach, and only if assistenten exist (prevent orphaned teams)
- Leave team: assistenten always; hoofdcoach only if ≥1 assistent remains
- Update team: any member

## Common Patterns

**Team Context**: Controllers set data via `session('current_team_id')`. Switch team via `TeamController::switch()`. First team auto-set as default in `team_user.is_default`.

**Formation Validation**: Use `ValidFormation` rule - checks `lineup_formation` sum equals `total_players - 1` (excluding keeper).

**Opponent Integration**: Teams link to single `opponent_id` for club info (name, logo, maps_location). Shared globally, fetched from external API.

**Match Goals**: `match_goals` table tracks scorer, optional assist, minute, subtype. Many-to-one with players and matches.

## Testing Conventions

Tests use Pest PHP (`tests/Feature/`, `tests/Unit/`). Auth tests from Laravel Breeze. Write feature tests for policies and service logic. No extensive test suite yet - good opportunity to add coverage.

## Dutch Language

UI, variable names, and comments mix Dutch (team names, UI labels) and English (code). Maintain consistency: Dutch for domain terms (hoofdcoach, assistent, tegenstander), English for code constructs.
