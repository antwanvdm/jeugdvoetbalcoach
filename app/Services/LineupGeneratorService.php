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
    private Collection $benchCounts; // Bench counts from last 3 matches

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
        
        // Get bench counts from last 3 matches for fair rotation
        $this->benchCounts = $this->getRecentBenchCounts($currentMatch);
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
     * Get bench counts for each player from the last 3 matches
     */
    private function getRecentBenchCounts(FootballMatch $currentMatch): Collection
    {
        // Get the last 3 matches before the current one
        $lastThreeMatches = FootballMatch::where('season_id', $currentMatch->season_id)
            ->where('id', '!=', $currentMatch->id)
            ->orderBy('date', 'desc')
            ->limit(3)
            ->pluck('id');

        if ($lastThreeMatches->isEmpty()) {
            // No previous matches, return empty counts
            return collect();
        }

        // Count bench appearances (position_id = null) for each player in these matches
        $benchCounts = Player::query()
            ->withCount([
                'footballMatches as bench_count' => function ($q) use ($lastThreeMatches) {
                    $q->whereIn('football_match_player.football_match_id', $lastThreeMatches)
                        ->whereNull('football_match_player.position_id');
                }
            ])
            ->pluck('bench_count', 'id');

        $this->debugLog('Bench counts from last 3 matches:', $benchCounts->toArray());

        return $benchCounts;
    }

    /**
     * Select 4 keepers based on historical appearances and last match rotation
     */
    private function selectKeepers(): Collection
    {
        // Debug: Log last match keepers
        $this->debugLog('Last match keepers:', $this->lastMatchKeepers->toArray());

        // Filter players whose favorite position is keeper
        $keeperPlayers = $this->players->filter(function ($player) {
            return $this->getPlayerFavoriteRole($player->position_id) === 'keeper';
        });

        // If there are favorite keepers, use only those
        if ($keeperPlayers->isNotEmpty()) {
            // Sort by who was least recently keeper and least often
            $sortedKeepers = $keeperPlayers->sortBy(function ($player) {
                $wasLastMatchKeeper = $this->lastMatchKeepers->contains($player->id) ? 1 : 0;
                $historicalCount = (int)$this->keeperCounts->get($player->id, 0);
                $this->debugLog("Player {$player->name}: wasLastMatchKeeper={$wasLastMatchKeeper}, historicalCount={$historicalCount}");
                return [$wasLastMatchKeeper, $historicalCount, $player->name];
            });
            // Take all favorite keepers
            return $sortedKeepers->values();
        }
        // No favorite keepers: fallback to old logic
        $sortedKeepers = $this->players->sortBy(function ($player) {
            $wasLastMatchKeeper = $this->lastMatchKeepers->contains($player->id) ? 1 : 0;
            $historicalCount = (int)$this->keeperCounts->get($player->id, 0);
            $this->debugLog("Player {$player->name}: wasLastMatchKeeper={$wasLastMatchKeeper}, historicalCount={$historicalCount}");
            return [$wasLastMatchKeeper, $historicalCount, $player->name];
        });
        return $sortedKeepers->take(4)->values();
    }

    /**
     * Map keepers to their designated quarters
     */
    private function mapKeepersToQuarters(Collection $keepers): array
    {
        // Distribute keepers over 4 quarters, repeat if necessary
        $keeperCount = $keepers->count();
        $mapping = [];
        for ($q = 1; $q <= 4; $q++) {
            if ($keeperCount > 0) {
                $keeper = $keepers[($q - 1) % $keeperCount];
                $mapping[$q] = $keeper->id;
            }
        }
        return $mapping;
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

        // Count favorite keepers (position_id = 1)
        $favoriteKeepersCount = $keepers->filter(fn($k) => $this->getPlayerFavoriteRole($k->position_id) === 'keeper')->count();

        $keeperBenchPlan = [];

        // Logic based on number of favorite keepers:
        // - 0 keepers: old logic, everyone can be keeper, keepers get 1 bench quarter
        // - 1 keeper: keeper always plays, never benched
        // - 2+ keepers: keepers only play keeper, but get normal bench rotation
        if ($favoriteKeepersCount === 0 && $keepers->isNotEmpty() && $totalBenchesNeeded > 0) {
            // No favorite keepers: old logic where keepers get 1 bench quarter
            foreach ($keepers as $index => $keeper) {
                $keeperQuarter = $index + 1;
                $benchQuarter = $this->calculateKeeperBenchQuarter($index, $keeperQuarter);
                $keeperBenchPlan[$keeper->id] = [$benchQuarter];
            }
        } elseif ($favoriteKeepersCount >= 2 && $totalBenchesNeeded > 0) {
            // 2+ keepers: keepers also need bench turns (like other players)
            // We treat them as non-keepers for bench rotation
            foreach ($keepers as $index => $keeper) {
                $keeperQuarter = ($index % 4) + 1; // Their keeper quarter
                // They can't be benched in their keeper quarter, but can be in other quarters
                // We let the normal distribution handle this later
            }
        }
        // If $favoriteKeepersCount === 1: keeper gets NO bench quarter (stays empty in keeperBenchPlan)

        // 2) Calculate how many benches remain per quarter after assigning keeper benches
        $keeperBenchCounts = $this->computeKeeperBenchCounts($keeperBenchPlan);
        $remainingPerQuarter = [];
        foreach ([1, 2, 3, 4] as $q) {
            $remainingPerQuarter[$q] = max(0, ($targetsPerQuarter[$q] ?? 0) - ($keeperBenchCounts[$q] ?? 0));
        }

        // 3) Distribute remaining benches among non-keepers evenly and deterministically
        // With 2+ favorite keepers, keepers must also participate in bench rotation
        if ($favoriteKeepersCount >= 2) {
            // All players (including keepers) participate in bench rotation
            $playersForBenchRotation = $this->players;
        } else {
            // Keepers are already handled or not present, only non-keepers
            $playersForBenchRotation = $this->players->reject(fn($p) => in_array($p->id, $keepers->pluck('id')->all(), true));
        }

        $nonKeeperBenchPlan = $this->distributeNonKeeperBenches($playersForBenchRotation, $remainingPerQuarter, $keepers);

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
     * @param Collection $nonKeepers Players to distribute bench slots for
     * @param array $remainingPerQuarter Remaining bench slots per quarter
     * @param Collection $keepers Collection of keepers (to check their keeping quarters)
     */
    private function distributeNonKeeperBenches(Collection $nonKeepers, array $remainingPerQuarter, Collection $keepers = null): array
    {
        $benchPlan = [];

        // Sort players by bench count (ascending) so those with fewer benches get priority
        // Secondary sort by weight and name for determinism
        $ordered = $nonKeepers->sortBy(function ($player) {
            $benchCount = $this->benchCounts->get($player->id, 0);
            return [$benchCount, $player->weight, $player->name];
        })->values();

        $totalRemaining = array_sum($remainingPerQuarter);
        if ($totalRemaining <= 0 || $ordered->isEmpty()) {
            return $benchPlan;
        }

        // Build a map of which quarters each keeper is keeping
        // This prevents keepers from being benched during their keeper quarters
        $keeperQuarterMap = [];
        if ($keepers && $keepers->isNotEmpty()) {
            $favoriteKeepers = $keepers->filter(fn($k) => $this->getPlayerFavoriteRole($k->position_id) === 'keeper');
            $favoriteKeepersCount = $favoriteKeepers->count();
            
            // Map ALL keepers to their quarters (not just 2+ favorites)
            if ($favoriteKeepersCount === 1) {
                // Single keeper keeps all quarters - exclude from bench entirely
                $keeperQuarterMap[$favoriteKeepers->first()->id] = 'all'; // Special marker
            } else if ($favoriteKeepersCount >= 2) {
                // Build reverse mapping from mapKeepersToQuarters
                // keeper_id => [array of quarters they keep]
                $keeperCount = $favoriteKeepers->count();
                foreach ($favoriteKeepers->values() as $index => $keeper) {
                    $keeperQuarters = [];
                    for ($q = 1; $q <= 4; $q++) {
                        if (($q - 1) % $keeperCount === $index) {
                            $keeperQuarters[] = $q;
                        }
                    }
                    $keeperQuarterMap[$keeper->id] = $keeperQuarters;
                }
            }
        }

        while ($totalRemaining > 0) {
            foreach ($ordered as $player) {
                if ($totalRemaining <= 0) {
                    break;
                }

                $already = $benchPlan[$player->id] ?? [];

                // If this player is a keeper, they can't be benched in their keeper quarter
                $excludedQuarter = $keeperQuarterMap[$player->id] ?? null;

                $quarter = $this->pickQuarterForPlayer($already, $remainingPerQuarter, $excludedQuarter);
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
     * @param array $alreadyAssignedQuarters Quarters already assigned to this player
     * @param array $remainingPerQuarter Remaining bench slots per quarter
     * @param int|string|array|null $excludedQuarter Quarter(s) that should be excluded (e.g., keeper's keeping quarters), or 'all' to exclude player entirely
     */
    private function pickQuarterForPlayer(array $alreadyAssignedQuarters, array $remainingPerQuarter, int|string|array|null $excludedQuarter = null): ?int
    {
        // If excludedQuarter is 'all', this player should never be benched (e.g., single keeper)
        if ($excludedQuarter === 'all') {
            return null;
        }
        
        // Normalize excludedQuarter to array for easier checking
        $excludedQuarters = [];
        if (is_int($excludedQuarter)) {
            $excludedQuarters = [$excludedQuarter];
        } elseif (is_array($excludedQuarter)) {
            $excludedQuarters = $excludedQuarter;
        }
        
        // Helper to check adjacency considering wrap-around (1 adjacent to 2 and 4)
        $isAdjacent = function (int $a, int $b): bool {
            return $a === $b - 1 || $a === $b + 1 || ($a === 4 && $b === 1) || ($a === 1 && $b === 4);
        };

        // Build candidate quarters with remaining slots, excluding ones already assigned and excluded quarters
        $candidates = [];
        foreach ($remainingPerQuarter as $q => $remaining) {
            if ($remaining > 0 && !in_array($q, $alreadyAssignedQuarters, true) && !in_array($q, $excludedQuarters, true)) {
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

        // Identify the keeper for this quarter
        $keeperId = $keeperByQuarter[$quarter] ?? null;

        // Mark benched players (position_id = null means on the bench)
        foreach ($this->players as $player) {
            if (in_array($quarter, $benchPlan[$player->id] ?? [], true)) {
                $quarterData->addAssignment($player->id, null);
            }
        }

        // Ensure favorite keepers who aren't keeping this quarter are benched
        if ($keeperId) {
            $favoriteKeepers = $this->players->filter(fn($p) => $this->getPlayerFavoriteRole($p->position_id) === 'keeper');
            foreach ($favoriteKeepers as $keeper) {
                // If this keeper is not the designated keeper for this quarter and not already assigned, bench them
                if ($keeper->id !== $keeperId && !$quarterData->isPlayerAssigned($keeper->id)) {
                    $quarterData->addAssignment($keeper->id, null);
                }
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

            $favoriteRole = $this->getPlayerFavoriteRole($player->position_id);

            // Keepers can ONLY play keeper role, never outfield
            if ($favoriteRole === 'keeper' && $role !== 'keeper') {
                return false;
            }

            if (!$preferredRoleOnly) {
                return true;
            }

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
            // Skip favorite keepers - they should never be assigned to outfield
            if ($this->getPlayerFavoriteRole($player->position_id) === 'keeper') {
                return false;
            }
            return !$quarterData->isPlayerSelected($player->id) && !$quarterData->isPlayerAssigned($player->id);
        });

        if ($remainingPlayers->isNotEmpty()) {
            $this->debugLog('Warning: Found unassigned players after formation assignment', [
                'quarter' => $quarterData->getQuarter(),
                'count' => $remainingPlayers->count(),
                'players' => $remainingPlayers->pluck('name')->toArray(),
            ]);

            foreach ($remainingPlayers as $player) {
                $quarterData->addAssignment($player->id, $player->position_id ?? self::DEFENDER_POSITION_ID);
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
