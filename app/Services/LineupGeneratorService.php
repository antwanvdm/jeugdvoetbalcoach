<?php

namespace App\Services;

use App\Models\FootballMatch;
use App\Models\Player;
use App\Services\LineupGenerator\AssignmentResult;
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

    private const FORMATION_NEEDS = [
        'defender' => 2,
        'midfielder' => 1,
        'attacker' => 2,
    ];

    private Collection $players;
    private Collection $keeperCounts;
    private Collection $lastMatchKeepers;

    /**
     * Generate lineup for a football match
     */
    public function generateLineup(FootballMatch $match): void
    {
        $this->loadPlayersData($match);

        $keepers = $this->selectKeepers();
        $keeperByQuarter = $this->mapKeepersToQuarters($keepers);
        $benchPlan = $this->createBenchPlan($keepers);

        $this->assignPlayersToQuarters($match, $keeperByQuarter, $benchPlan);
    }

    /**
     * Load all players and their keeper statistics
     */
    private function loadPlayersData(FootballMatch $currentMatch): void
    {
        $this->players = Player::whereHas('seasons', function ($q) use ($currentMatch) {
            $q->where('seasons.id', $currentMatch->season_id);
        })->inRandomOrder()->get();

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
     * Select 4 keepers based on historical appearances, last match rotation, and weight diversity
     */
    private function selectKeepers(): Collection
    {
        // Debug: Log last match keepers
        $this->debugLog('Last match keepers:', $this->lastMatchKeepers->toArray());

        // Sort by priority:
        // 1. Prefer players who didn't keep last match
        // 2. Then by historical keeper count (ascending)
        // 3. Then by name for consistency
        $sortedKeepers = $this->players->sortBy(function ($player) {
            $keptLastMatch = $this->lastMatchKeepers->contains($player->id) ? 1 : 0;
            $historicalCount = (int)$this->keeperCounts->get($player->id, 0);

            // Debug: Log sorting criteria for each player
            $this->debugLog("Player {$player->name}: keptLastMatch={$keptLastMatch}, historicalCount={$historicalCount}");

            return [$keptLastMatch, $historicalCount, $player->name];
        });

        $keepers = collect();
        $availableKeepers = $sortedKeepers->values();

        // Pick first keeper (best priority - didn't keep last match, least historical appearances)
        if ($availableKeepers->isNotEmpty()) {
            $firstKeeper = $availableKeepers->shift();
            $this->debugLog("Selected first keeper: {$firstKeeper->name}");
            $keepers->push($firstKeeper);
        }

        // Pick remaining 3 keepers considering weight diversity and rotation
        while ($keepers->count() < 4 && $availableKeepers->isNotEmpty()) {
            $bestCandidate = $this->findBestKeeperCandidate($keepers, $availableKeepers);

            if ($bestCandidate !== null) {
                $selectedKeeper = $availableKeepers->splice($bestCandidate, 1)->first();
                $keeperNumber = $keepers->count() + 1;
                $this->debugLog("Selected keeper {$keeperNumber}: {$selectedKeeper->name}");
                $keepers->push($selectedKeeper);
            } else {
                $fallbackKeeper = $availableKeepers->shift();
                $keeperNumber = $keepers->count() + 1;
                $this->debugLog("Selected fallback keeper {$keeperNumber}: {$fallbackKeeper->name}");
                $keepers->push($fallbackKeeper);
            }
        }

        // Final log of selected keepers
        $this->debugLog('Final selected keepers:', $keepers->pluck('name')->toArray());

        return $keepers;
    }

    /**
     * Find the best keeper candidate based on weight balance
     */
    private function findBestKeeperCandidate(Collection $currentKeepers, Collection $availableKeepers): ?int
    {
        $bestCandidate = null;
        $bestBalance = PHP_FLOAT_MAX;

        foreach ($availableKeepers as $index => $candidate) {
            $testGroup = $currentKeepers->push($candidate);
            $balance = $this->calculateWeightBalance($testGroup->pluck('id')->all());

            if ($balance < $bestBalance) {
                $bestBalance = $balance;
                $bestCandidate = $index;
            }

            $currentKeepers->pop(); // Remove test candidate
        }

        return $bestCandidate;
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
        $benchPlan = [];

        // Non-keepers: alternate Q1+Q3 vs Q2+Q4, considering weight distribution
        $nonKeepers = $this->players->reject(fn($p) => in_array($p->id, $keepers->pluck('id')->all(), true));
        $nonKeepersSorted = $nonKeepers->sortBy(['weight', 'name']);

        $toggle = false;
        foreach ($nonKeepersSorted as $player) {
            $benchPlan[$player->id] = $toggle ? [2, 4] : [1, 3];
            $toggle = !$toggle;
        }

        // Keepers: bench exactly once (not their keeper quarter)
        foreach ($keepers as $index => $keeper) {
            $keeperQuarter = $index + 1;
            $benchQuarter = $this->calculateKeeperBenchQuarter($index, $keeperQuarter);
            $benchPlan[$keeper->id] = [$benchQuarter];
        }

        return $benchPlan;
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

        // Mark benched players
        $quarterData = $this->withBenchedPlayers($quarterData, $benchPlan);

        // Get available players (not benched)
        $availablePlayers = $this->getAvailablePlayers($quarter, $benchPlan);

        // Assign keeper
        $quarterData = $this->withAssignedKeeper($quarterData, $keeperByQuarter);

        // Assign outfield players based on formation
        $quarterData = $this->withAssignedOutfieldPlayers($quarterData, $availablePlayers);

        // Assign remaining players
        $quarterData = $this->withAssignedRemainingPlayers($quarterData, $availablePlayers);

        return $quarterData->getAssignments();
    }

    /**
     * Add benched players to quarter data
     */
    private function withBenchedPlayers(QuarterAssignmentData $quarterData, array $benchPlan): QuarterAssignmentData
    {
        foreach ($this->players as $player) {
            if (in_array($quarterData->getQuarter(), $benchPlan[$player->id] ?? [], true)) {
                $quarterData->addAssignment($player->id, null);
            }
        }

        return $quarterData;
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
        $needs = self::FORMATION_NEEDS;

        // First pass: satisfy needs with players who prefer that role
        foreach (array_keys($needs) as $role) {
            while ($needs[$role] > 0) {
                $result = $this->tryAssignPlayerForRole($quarterData, $availablePlayers, $role, true);
                if ($result->wasSuccessful()) {
                    $quarterData = $result->getQuarterData();
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
                if ($result->wasSuccessful()) {
                    $quarterData = $result->getQuarterData();
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
    private function tryAssignPlayerForRole(QuarterAssignmentData $quarterData, Collection $availablePlayers, string $role, bool $preferredRoleOnly): AssignmentResult
    {
        $candidates = $this->getCandidatesForRole($availablePlayers, $quarterData->getSelectedPlayers(), $role, $preferredRoleOnly);

        if ($candidates->isEmpty()) {
            return AssignmentResult::failure($quarterData);
        }

        // Sort by weight balance impact
        $bestCandidate = $this->selectBestCandidateByWeight($candidates, $quarterData->getSelectedPlayers());

        // Assign the selected player
        $quarterData->addSelectedPlayer($bestCandidate->id, $role);
        $positionId = $this->determinePositionId($bestCandidate, $role);
        $quarterData->addAssignment($bestCandidate->id, $positionId);

        return AssignmentResult::success($quarterData);
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
     * Select the best candidate based on weight balance
     */
    private function selectBestCandidateByWeight(Collection $candidates, Collection $selectedPlayers): Player
    {
        return $candidates->sortBy(function ($candidate) use ($selectedPlayers) {
            $currentSelectedIds = $selectedPlayers->keys()->all();
            $balanceWithCandidate = $this->calculateWeightBalance(array_merge($currentSelectedIds, [$candidate->id]));

            // Return array for multi-level sorting: [balance_score, weight, name]
            return [$balanceWithCandidate, $candidate->weight, $candidate->name];
        })->first();
    }

    /**
     * Determine the appropriate position ID for a player in a role
     */
    private function determinePositionId(Player $player, string $role): int
    {
        $favoriteRole = $this->getPlayerFavoriteRole($player->position_id);

        // Use player's favorite position if it matches the role
        if ($favoriteRole === $role && $player->position_id) {
            return $player->position_id;
        }

        // Otherwise use the representative position for the role
        return self::ROLE_POSITION_MAP[$role];
    }

    /**
     * Assign remaining unassigned players
     */
    private function withAssignedRemainingPlayers(QuarterAssignmentData $quarterData, Collection $availablePlayers): QuarterAssignmentData
    {
        foreach ($availablePlayers as $player) {
            if ($quarterData->isPlayerSelected($player->id) || $quarterData->isPlayerAssigned($player->id)) {
                continue;
            }

            // Assign their favorite non-keeper position, or fallback to defender
            $positionId = $player->position_id;
            if ($this->getPlayerFavoriteRole($positionId) === 'keeper') {
                $positionId = self::DEFENDER_POSITION_ID;
            }

            $quarterData->addAssignment($player->id, $positionId);
        }

        return $quarterData;
    }

    /**
     * Get a player's favorite role based on their position
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
     * Calculate weight balance score for a group of players
     * Lower score = better balance
     */
    private function calculateWeightBalance(array $playerIds): float
    {
        if (empty($playerIds) || count($playerIds) <= 1) {
            return 0;
        }

        $selectedPlayers = $this->players->whereIn('id', $playerIds);
        $weights = $selectedPlayers->pluck('weight')->countBy();
        $totalPlayers = count($playerIds);

        // Penalty for having too many players with same weight
        $penalty = 0;
        foreach ($weights as $count) {
            if ($count > 2) {
                $penalty += ($count - 2) * 10; // Heavy penalty for clustering
            } elseif ($count > 1) {
                $penalty += ($count - 1) * 2; // Light penalty for pairs
            }
        }

        // Variance component for overall distribution
        $idealCountPerWeight = $totalPlayers / max(1, $weights->count());
        $variance = 0;
        foreach ($weights as $count) {
            $variance += pow($count - $idealCountPerWeight, 2);
        }

        return $penalty + ($variance * 0.5);
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
