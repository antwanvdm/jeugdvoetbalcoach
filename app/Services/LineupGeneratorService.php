<?php

namespace App\Services;

use App\Models\FootballMatch;
use App\Models\Player;
use App\Services\LineupGenerator\QuarterAssignmentData;
use Illuminate\Support\Collection;

class LineupGeneratorService
{
    private const KEEPER_POSITION_ID = 1;
    private const DEFENDER_POSITION_ID = 2;
    private const MIDFIELDER_POSITION_ID = 3;
    private const ATTACKER_POSITION_ID = 4;

    private const ROLE_POSITION_MAP = [
        'keeper' => self::KEEPER_POSITION_ID,
        'defender' => self::DEFENDER_POSITION_ID,
        'midfielder' => self::MIDFIELDER_POSITION_ID,
        'attacker' => self::ATTACKER_POSITION_ID,
    ];


    private Collection $players;
    private Collection $keeperCounts;
    private Collection $lastMatchKeepers;

    // Dynamic formation context (set per match): outfield needs; total on-field count is computed dynamically
    private array $formationNeeds = [];
    private ?int $formationTotalPlayers = null;

    /**
     * Generate lineup for a football match
     * 
     * @param FootballMatch $match
     * @param array $availablePlayerIds Optional array of player IDs that are available for this match
     */
    public function generateLineup(FootballMatch $match, array $availablePlayerIds = []): void
    {
        // Apply formation from the match's season (dynamic desired players on field and role needs)
        $this->applyFormationFromMatch($match);

        $this->loadPlayersData($match, $availablePlayerIds);

        $keepers = $this->selectKeepers();
        $keeperByQuarter = $this->mapKeepersToQuarters($keepers);
        $benchPlan = $this->createBenchPlan($keepers);

        $this->assignPlayersToQuarters($match, $keeperByQuarter, $benchPlan);
    }

    /**
     * Load all players and their keeper statistics
     * 
     * @param FootballMatch $currentMatch
     * @param array $availablePlayerIds Optional array of player IDs to filter by
     */
    private function loadPlayersData(FootballMatch $currentMatch, array $availablePlayerIds = []): void
    {
        $query = Player::whereHas('seasons', function ($q) use ($currentMatch) {
            $q->where('seasons.id', $currentMatch->season_id);
        });

        // Filter by available players if specified
        if (!empty($availablePlayerIds)) {
            $query->whereIn('id', $availablePlayerIds);
        }

        $this->players = $query->inRandomOrder()->get();

        // Get historical keeper counts (exclude current match if it has data)
        $this->keeperCounts = Player::query()
            ->withCount([
                'footballMatches as keeper_count' => function ($q) use ($currentMatch) {
                    $q->where('football_match_player.position_id', self::KEEPER_POSITION_ID)
                        ->where('football_match_player.football_match_id', '!=', $currentMatch->id);
                }
            ])
            ->pluck('keeper_count', 'id');

        // Get keepers from the last match (most recent 4 quarters, excluding current match)
        $this->lastMatchKeepers = $this->getLastMatchKeepers($currentMatch);
    }

    /**
     * Get players who kept goal in the last match (excluding current match)
     */
    private function getLastMatchKeepers(FootballMatch $currentMatch): Collection
    {
        $lastMatch = FootballMatch::where('id', '!=', $currentMatch->id)
            ->latest('date')
            ->first();

        $this->debugLog("Current match ID: {$currentMatch->id}");
        $this->debugLog("Last match found: " . ($lastMatch ? "ID {$lastMatch->id}, Date: {$lastMatch->date}" : "None"));

        if (!$lastMatch) {
            return collect();
        }

        $lastMatchKeepers = Player::query()
            ->join('football_match_player', 'players.id', '=', 'football_match_player.player_id')
            ->where('football_match_player.football_match_id', $lastMatch->id)
            ->where('football_match_player.position_id', self::KEEPER_POSITION_ID)
            ->pluck('players.id');

        $this->debugLog("Found keepers from last match:", $lastMatchKeepers->toArray());

        return $lastMatchKeepers;
    }

    /**
     * Select 4 keepers based on historical appearances and last match rotation
     */
    private function selectKeepers(): Collection
    {
        // Debug: Log last match keepers
        $this->debugLog('Last match keepers:', $this->lastMatchKeepers->toArray());

        // Sort by priority:
        // 1. Prefer players who didn't keep last match (0 = didn't keep, 1 = did keep)
        // 2. Then by historical keeper count (ascending)
        // 3. Then by name for consistency
        $sortedKeepers = $this->players->sortBy(function ($player) {
            $wasLastMatchKeeper = $this->lastMatchKeepers->contains($player->id) ? 1 : 0;
            $historicalCount = (int)$this->keeperCounts->get($player->id, 0);

            // Debug: Log sorting criteria for each player
            $this->debugLog("Player {$player->name}: wasLastMatchKeeper={$wasLastMatchKeeper}, historicalCount={$historicalCount}");

            return [$wasLastMatchKeeper, $historicalCount, $player->name];
        });

        // Simply take the first 4 from the sorted list
        $keepers = $sortedKeepers->take(4)->values();

        // Log selected keepers
        $this->debugLog('Final selected keepers:', $keepers->pluck('name')->toArray());

        return $keepers;
    }

    /**
     * Map keepers to their designated quarters
     */
    private function mapKeepersToQuarters(Collection $keepers): array
    {
        return $keepers->mapWithKeys(fn($keeper, $index) => [$index + 1 => $keeper->id])->toArray();
    }

    /**
     * Create bench plan for all players
     */
    private function createBenchPlan(Collection $keepers): array
    {
        // Target: exactly desiredOnField players on the field each quarter (dynamic from formation)
        $targetsPerQuarter = $this->computePerQuarterBenchTargets();

        // Total benches needed across all quarters
        $totalBenchesNeeded = array_sum($targetsPerQuarter);

        // 1) Keepers: only bench if there are benches needed
        $keeperBenchPlan = [];
        if ($totalBenchesNeeded > 0) {
            foreach ($keepers as $index => $keeper) {
                $keeperQuarter = $index + 1;
                $benchQuarter = $this->calculateKeeperBenchQuarter($index, $keeperQuarter);
                $keeperBenchPlan[$keeper->id] = [$benchQuarter];
            }
        }

        // 2) Calculate how many benches remain per quarter after assigning keeper benches
        $keeperBenchCounts = $this->computeKeeperBenchCounts($keeperBenchPlan);
        $remainingPerQuarter = [];
        foreach ([1, 2, 3, 4] as $q) {
            $remainingPerQuarter[$q] = max(0, ($targetsPerQuarter[$q] ?? 0) - ($keeperBenchCounts[$q] ?? 0));
        }

        // 3) Distribute remaining benches among non-keepers evenly and deterministically
        $nonKeepers = $this->players->reject(fn($p) => in_array($p->id, $keepers->pluck('id')->all(), true));
        $nonKeeperBenchPlan = $this->distributeNonKeeperBenches($nonKeepers, $remainingPerQuarter);

        // 4) Merge plans
        $benchPlan = $nonKeeperBenchPlan + $keeperBenchPlan;

        // Debug
        $this->debugLog('Bench targets per quarter', $targetsPerQuarter);
        $this->debugLog('Total benches needed', ['total' => $totalBenchesNeeded]);
        $this->debugLog('Keeper bench counts per quarter', $keeperBenchCounts);
        $this->debugLog('Remaining benches per quarter (for non-keepers)', $remainingPerQuarter);

        return $benchPlan;
    }

    /**
     * Compute per-quarter bench targets based on squad size and desired on-field count (dynamic).
     */
    private function computePerQuarterBenchTargets(): array
    {
        $totalPlayers = $this->players->count();
        $desiredOnField = $this->getDesiredOnField(); // dynamic: computed from formation or fallback
        $benchesPerQuarter = max(0, $totalPlayers - $desiredOnField);

        return [
            1 => $benchesPerQuarter,
            2 => $benchesPerQuarter,
            3 => $benchesPerQuarter,
            4 => $benchesPerQuarter,
        ];
    }

    /**
     * Count how many keepers are benched per quarter from the keeper bench plan.
     * @param array $keeperBenchPlan [playerId => [quarter]]
     */
    private function computeKeeperBenchCounts(array $keeperBenchPlan): array
    {
        $counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        foreach ($keeperBenchPlan as $quarters) {
            foreach ($quarters as $q) {
                if (isset($counts[$q])) {
                    $counts[$q]++;
                }
            }
        }
        return $counts;
    }

    /**
     * Distribute remaining bench slots across non-keepers as evenly as possible.
     */
    private function distributeNonKeeperBenches(Collection $nonKeepers, array $remainingPerQuarter): array
    {
        $benchPlan = [];

        // Deterministic ordering for fairness
        $ordered = $nonKeepers->sortBy(['weight', 'name'])->values();

        $totalRemaining = array_sum($remainingPerQuarter);
        if ($totalRemaining <= 0 || $ordered->isEmpty()) {
            return $benchPlan;
        }

        while ($totalRemaining > 0) {
            foreach ($ordered as $player) {
                if ($totalRemaining <= 0) {
                    break;
                }

                $already = $benchPlan[$player->id] ?? [];
                $quarter = $this->pickQuarterForPlayer($already, $remainingPerQuarter);
                if ($quarter === null) {
                    // No quarter left that this player hasn't already been assigned → skip
                    continue;
                }

                $benchPlan[$player->id] = array_values(array_unique(array_merge($already, [$quarter])));
                $remainingPerQuarter[$quarter]--;
                $totalRemaining--;
            }
        }

        return $benchPlan;
    }

    /**
     * Pick the best quarter for a player to be benched next, preferring quarters with most remaining need.
     * Avoids assigning the same quarter twice to the same player.
     */
    private function pickQuarterForPlayer(array $alreadyAssignedQuarters, array $remainingPerQuarter): ?int
    {
        // Helper to check adjacency considering wrap-around (1 adjacent to 2 and 4)
        $isAdjacent = function (int $a, int $b): bool {
            return $a === $b - 1 || $a === $b + 1 || ($a === 4 && $b === 1) || ($a === 1 && $b === 4);
        };

        // Build candidate quarters with remaining slots, excluding ones already assigned
        $candidates = [];
        foreach ($remainingPerQuarter as $q => $remaining) {
            if ($remaining > 0 && !in_array($q, $alreadyAssignedQuarters, true)) {
                $candidates[$q] = $remaining;
            }
        }

        if (empty($candidates)) {
            return null;
        }

        // First pass: prefer quarters that are NOT adjacent to any already assigned quarter for this player
        $nonAdjacent = [];
        foreach ($candidates as $q => $remaining) {
            $adjacentToExisting = false;
            foreach ($alreadyAssignedQuarters as $assigned) {
                if ($isAdjacent($q, (int)$assigned)) {
                    $adjacentToExisting = true;
                    break;
                }
            }
            if (!$adjacentToExisting) {
                $nonAdjacent[$q] = $remaining;
            }
        }

        // If only adjacent quarters remain, enforce the rule by skipping assignment now
        if (empty($nonAdjacent)) {
            return null;
        }

        // Choose the quarter with the highest remaining; tie-breaker by quarter number
        arsort($nonAdjacent); // descending by remaining
        $bestQuarter = array_key_first($nonAdjacent);
        return (int)$bestQuarter;
    }

    /**
     * Calculate which quarter a keeper should be benched
     */
    private function calculateKeeperBenchQuarter(int $keeperIndex, int $keeperQuarter): int
    {
        $candidate = ($keeperIndex % 4) + 2; // yields 2,3,4,5 -> mod 4 to 1..4
        $benchQuarter = (($candidate - 1) % 4) + 1;

        if ($benchQuarter === $keeperQuarter) {
            $benchQuarter = ($benchQuarter % 4) + 1; // next quarter
        }

        return $benchQuarter;
    }

    /**
     * Assign players to quarters based on formation and bench plan
     */
    private function assignPlayersToQuarters(FootballMatch $match, array $keeperByQuarter, array $benchPlan): void
    {
        foreach (range(1, 4) as $quarter) {
            $assignments = $this->buildQuarterAssignments($quarter, $keeperByQuarter, $benchPlan);

            if (!empty($assignments)) {
                $match->players()->attach($assignments);
            }
        }
    }

    /**
     * Build player assignments for a specific quarter
     */
    private function buildQuarterAssignments(int $quarter, array $keeperByQuarter, array $benchPlan): array
    {
        $quarterData = new QuarterAssignmentData($quarter);

        // Mark benched players (position_id = null means on the bench)
        foreach ($this->players as $player) {
            if (in_array($quarter, $benchPlan[$player->id] ?? [], true)) {
                $quarterData->addAssignment($player->id, null);
            }
        }

        // Get available players (not benched)
        $availablePlayers = $this->getAvailablePlayers($quarter, $benchPlan);

        // Assign keeper
        $quarterData = $this->withAssignedKeeper($quarterData, $keeperByQuarter);

        // Assign outfield players based on formation
        $quarterData = $this->withAssignedOutfieldPlayers($quarterData, $availablePlayers);

        // Assign any remaining unassigned players (fallback for incomplete formations)
        $quarterData = $this->withAssignedRemainingPlayers($quarterData, $availablePlayers);

        return $quarterData->getAssignments();
    }

    /**
     * Get players available to play this quarter (not benched)
     */
    private function getAvailablePlayers(int $quarter, array $benchPlan): Collection
    {
        return $this->players->reject(function ($player) use ($benchPlan, $quarter) {
            return in_array($quarter, $benchPlan[$player->id] ?? [], true);
        })->values();
    }

    /**
     * Assign keeper for the quarter
     */
    private function withAssignedKeeper(QuarterAssignmentData $quarterData, array $keeperByQuarter): QuarterAssignmentData
    {
        $keeperId = $keeperByQuarter[$quarterData->getQuarter()] ?? null;
        if ($keeperId && !$quarterData->isPlayerAssigned($keeperId)) {
            $quarterData->addSelectedPlayer($keeperId, 'keeper');
            $quarterData->addAssignment($keeperId, self::KEEPER_POSITION_ID);
        }

        return $quarterData;
    }

    /**
     * Assign outfield players based on formation needs
     */
    private function withAssignedOutfieldPlayers(QuarterAssignmentData $quarterData, Collection $availablePlayers): QuarterAssignmentData
    {
        $needs = $this->formationNeeds;

        // First pass: satisfy needs with players who prefer that role
        foreach (array_keys($needs) as $role) {
            while ($needs[$role] > 0) {
                $result = $this->tryAssignPlayerForRole($quarterData, $availablePlayers, $role, true);
                if ($result !== null) {
                    $quarterData = $result;
                    $needs[$role]--;
                } else {
                    break;
                }
            }
        }

        // Second pass: fill remaining slots with any available players
        foreach (array_keys($needs) as $role) {
            while ($needs[$role] > 0) {
                $result = $this->tryAssignPlayerForRole($quarterData, $availablePlayers, $role, false);
                if ($result !== null) {
                    $quarterData = $result;
                    $needs[$role]--;
                } else {
                    break;
                }
            }
        }

        return $quarterData;
    }

    /**
     * Try to assign a player for a specific role
     */
    private function tryAssignPlayerForRole(QuarterAssignmentData $quarterData, Collection $availablePlayers, string $role, bool $preferredRoleOnly): ?QuarterAssignmentData
    {
        $candidates = $this->getCandidatesForRole($availablePlayers, $quarterData->getSelectedPlayers(), $role, $preferredRoleOnly);

        if ($candidates->isEmpty()) {
            return null;
        }

        // Simple selection: sort by weight, then name for fairness
        $bestCandidate = $candidates->sortBy(['weight', 'name'])->first();

        // Assign the selected player
        $quarterData->addSelectedPlayer($bestCandidate->id, $role);
        $positionId = $this->getPositionForRole($bestCandidate, $role);
        $quarterData->addAssignment($bestCandidate->id, $positionId);

        return $quarterData;
    }

    /**
     * Get candidates for a specific role
     */
    private function getCandidatesForRole(Collection $availablePlayers, Collection $selectedPlayers, string $role, bool $preferredRoleOnly): Collection
    {
        return $availablePlayers->filter(function ($player) use ($selectedPlayers, $role, $preferredRoleOnly) {
            if (isset($selectedPlayers[$player->id])) {
                return false;
            }

            if (!$preferredRoleOnly) {
                return true;
            }

            $favoriteRole = $this->getPlayerFavoriteRole($player->position_id);
            return $favoriteRole === $role;
        });
    }

    /**
     * Get the appropriate position ID for a player in a role
     * Uses player's favorite position if it matches the role, otherwise uses role default
     */
    private function getPositionForRole(Player $player, string $role): int
    {
        $favoriteRole = match ($player->position_id) {
            self::KEEPER_POSITION_ID => 'keeper',
            self::DEFENDER_POSITION_ID => 'defender',
            self::MIDFIELDER_POSITION_ID => 'midfielder',
            self::ATTACKER_POSITION_ID => 'attacker',
            default => null
        };

        // Use player's favorite position if it matches the role
        if ($favoriteRole === $role && $player->position_id) {
            return $player->position_id;
        }

        // Otherwise use the representative position for the role
        return self::ROLE_POSITION_MAP[$role];
    }

    /**
     * Get a player's favorite role based on their position (for filtering)
     */
    private function getPlayerFavoriteRole(?int $positionId): ?string
    {
        return match ($positionId) {
            self::KEEPER_POSITION_ID => 'keeper',
            self::DEFENDER_POSITION_ID => 'defender',
            self::MIDFIELDER_POSITION_ID => 'midfielder',
            self::ATTACKER_POSITION_ID => 'attacker',
            default => null
        };
    }

    /**
     * Assign remaining unassigned players (fallback for incomplete formations)
     * This should rarely be needed if formations are properly configured
     */
    private function withAssignedRemainingPlayers(QuarterAssignmentData $quarterData, Collection $availablePlayers): QuarterAssignmentData
    {
        $remainingPlayers = $availablePlayers->filter(function ($player) use ($quarterData) {
            return !$quarterData->isPlayerSelected($player->id) && !$quarterData->isPlayerAssigned($player->id);
        });

        if ($remainingPlayers->isNotEmpty()) {
            $this->debugLog('Warning: Found unassigned players after formation assignment', [
                'quarter' => $quarterData->getQuarter(),
                'count' => $remainingPlayers->count(),
                'players' => $remainingPlayers->pluck('name')->toArray(),
            ]);

            foreach ($remainingPlayers as $player) {
                // Assign their favorite non-keeper position, or fallback to defender
                $positionId = $player->position_id;
                if ($this->getPlayerFavoriteRole($positionId) === 'keeper') {
                    $positionId = self::DEFENDER_POSITION_ID;
                }

                $quarterData->addAssignment($player->id, $positionId);
            }
        }

        return $quarterData;
    }

    /**
     * Apply formation from the match's season to set dynamic needs and desired on-field count.
     */
    private function applyFormationFromMatch(FootballMatch $match): void
    {
        $formation = optional($match->season)->formation;
        if (!$formation) {
            // Fallback when no formation configured on season: no specific outfield role needs
            $this->formationNeeds = ['defender' => 0, 'midfielder' => 0, 'attacker' => 0];
            $this->formationTotalPlayers = null;
            $this->debugLog('No formation found on season; using generic fallback', [
                'needs' => $this->formationNeeds,
                'desiredOnField' => $this->getDesiredOnField(),
            ]);
            return;
        }

        $needs = $this->parseFormationNeeds((string) $formation->lineup_formation);
        $this->formationNeeds = $needs;

        $sumOutfield = array_sum($needs);
        $totalPlayersFromFormation = (int) $formation->total_players;
        $this->formationTotalPlayers = $totalPlayersFromFormation > 0 ? $totalPlayersFromFormation : null;

        // Optional: log mismatch with stored total_players for visibility only
        $expectedOutfield = $totalPlayersFromFormation > 0 ? $totalPlayersFromFormation - 1 : null;
        $actualOutfield = $sumOutfield;

        $this->debugLog('Applied formation from season', [
            'lineup_formation' => $formation->lineup_formation,
            'needs' => $needs,
            'desiredOnField' => $this->getDesiredOnField(),
            'formation_total_players' => $totalPlayersFromFormation,
            'outfield_sum_matches_total_players_minus_keeper' => is_null($expectedOutfield) ? null : ($expectedOutfield === $actualOutfield),
        ]);
    }

    /**
     * Parse a lineup formation string (e.g., "2-1-2") into role needs.
     * Supports 2 or more parts; parts after the second are summed into attackers.
     */
    private function parseFormationNeeds(string $lineup): array
    {
        $parts = array_values(array_filter(array_map('trim', explode('-', $lineup)), fn($p) => $p !== ''));
        $nums = [];
        foreach ($parts as $p) {
            if (is_numeric($p)) {
                $nums[] = max(0, (int) $p);
            }
        }

        if (empty($nums)) {
            // Fallback: no specific outfield needs from lineup string
            return ['defender' => 0, 'midfielder' => 0, 'attacker' => 0];
        }

        $def = $nums[0] ?? 0;
        $mid = $nums[1] ?? 0;
        $att = 0;
        if (count($nums) >= 3) {
            $att = array_sum(array_slice($nums, 2));
        }

        return [
            'defender' => $def,
            'midfielder' => $mid,
            'attacker' => $att,
        ];
    }

    /**
     * Compute desired number of players on the field per quarter.
     * Priority:
     * 1) If formationTotalPlayers is set (>0), use it.
     * 2) Else if formationNeeds sum > 0, return 1 (keeper) + sum(needs).
     * 3) Else fallback to classic 6.
     */
    private function getDesiredOnField(): int
    {
        if (!is_null($this->formationTotalPlayers) && $this->formationTotalPlayers > 0) {
            return $this->formationTotalPlayers;
        }

        $sumOutfield = array_sum($this->formationNeeds);
        if ($sumOutfield > 0) {
            return 1 + $sumOutfield;
        }

        return 6;
    }

    /**
     * Debug logging helper
     */
    private function debugLog(string $message, array $context = []): void
    {
        if (config('app.debug')) {
            \Log::info($message, $context);
        }
    }
}
