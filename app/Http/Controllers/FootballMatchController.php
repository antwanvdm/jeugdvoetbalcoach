<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Models\Opponent;
use App\Models\Player;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FootballMatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $footballMatches = FootballMatch::with('opponent')->orderByDesc('date')->paginate(15);
        return view('football_matches.index', compact('footballMatches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $opponents = Opponent::orderBy('name')->pluck('name', 'id');
        return view('football_matches.create', compact('opponents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'opponent_id' => ['required', 'exists:opponents,id'],
            'home' => ['required', 'boolean'],
            'goals_scores' => ['nullable', 'integer', 'min:0'],
            'goals_conceded' => ['nullable', 'integer', 'min:0'],
            'date' => ['required', 'date'],
        ]);
        $match = FootballMatch::create($validated);

        // Auto-generate line-up for 4 quarters according to rules
        // 1) Pick 4 goalkeepers with least historical keeper appearances
        $keeperPositionId = 1;
        $defenderPositionId = 2;
        $midfielderPositionId = 3;
        $attackerPositionId = 4;

        $nonKeeperPositionIds = [$defenderPositionId, $midfielderPositionId, $attackerPositionId];
        $fallbackOutfieldPositionId = $nonKeeperPositionIds[0];
        $repRolePos = [
            'keeper' => $keeperPositionId,
            'defender' => $defenderPositionId,
            'midfielder' => $midfielderPositionId,
            'attacker' => $attackerPositionId,
        ];

        $players = Player::inRandomOrder()->get();

        // Helper to determine a player's favorite role
        $positionIdToRole = function (?int $pid) use ($keeperPositionId, $defenderPositionId, $midfielderPositionId, $attackerPositionId) {
            return match ($pid) {
                $keeperPositionId => 'keeper',
                $defenderPositionId => 'defender',
                $midfielderPositionId => 'midfielder',
                $attackerPositionId => 'attacker',
                default => null
            };
        };

        // Helper to calculate weight balance score for a group of players
        $calculateWeightBalance = function ($playerIds) use ($players) {
            if (empty($playerIds)) return 0;

            $selectedPlayers = $players->whereIn('id', $playerIds);
            $weights = $selectedPlayers->pluck('weight')->countBy();
            $totalPlayers = count($playerIds);

            if ($totalPlayers <= 1) return 0;

            // Calculate penalty for having too many players with same weight
            $penalty = 0;
            foreach ($weights as $weight => $count) {
                if ($count > 2) { // More than 2 players with same weight is less desirable
                    $penalty += ($count - 2) * 10; // Heavy penalty for clustering
                } elseif ($count > 1) {
                    $penalty += ($count - 1) * 2; // Light penalty for pairs
                }
            }

            // Add variance component for overall distribution
            $idealCountPerWeight = $totalPlayers / max(1, $weights->count());
            $variance = 0;
            foreach ($weights as $count) {
                $variance += pow($count - $idealCountPerWeight, 2);
            }

            return $penalty + ($variance * 0.5);
        };

        // Historical keeper counts per player
        $keeperCounts = Player::query()
            ->withCount([
                'footballMatches as keeper_count' => fn($q) => $q->where('football_match_player.position_id', $keeperPositionId)
            ])
            ->pluck('keeper_count', 'id');

        // Choose 4 keepers with the least historical keeper counts, also considering weight diversity
        $sortedKeepers = $players->sortBy(fn($p) => [(int)$keeperCounts->get($p->id, 0), $p->name]);

        // Select keepers with better weight distribution
        $keepers = collect();
        $availableKeepers = $sortedKeepers->values();

        // Pick first keeper (least appearances)
        if ($availableKeepers->isNotEmpty()) {
            $keepers->push($availableKeepers->shift());
        }

        // Pick remaining 3 keepers considering weight diversity
        while ($keepers->count() < 4 && $availableKeepers->isNotEmpty()) {
            $bestCandidate = null;
            $bestBalance = PHP_FLOAT_MAX;

            foreach ($availableKeepers as $index => $candidate) {
                $testGroup = $keepers->push($candidate);
                $balance = $calculateWeightBalance($testGroup->pluck('id')->all());

                if ($balance < $bestBalance) {
                    $bestBalance = $balance;
                    $bestCandidate = $index;
                }

                $keepers->pop(); // Remove test candidate
            }

            if ($bestCandidate !== null) {
                $keepers->push($availableKeepers->splice($bestCandidate, 1)->first());
            } else {
                // Fallback: just take next available
                $keepers->push($availableKeepers->shift());
            }
        }

        $keeperByQuarter = $keepers->mapWithKeys(fn($keeper, $index) => [$index + 1 => $keeper->id])->toArray();

        // Bench plan per player per quarter
        $benchPlan = []; // [player_id => [quarters...]]

        // Non-keepers: bench exactly 2 times, not consecutive -> alternate Q1+Q3 vs Q2+Q4
        // Consider weight distribution when assigning bench patterns
        $nonKeepers = $players->reject(fn($p) => in_array($p->id, $keepers->pluck('id')->all(), true));

        // Sort non-keepers by weight to help distribute them evenly across quarters
        $nonKeepersSorted = $nonKeepers->sortBy(['weight', 'name']);

        $toggle = false;
        foreach ($nonKeepersSorted as $p) {
            if ($toggle) {
                $benchPlan[$p->id] = [2, 4];
            } else {
                $benchPlan[$p->id] = [1, 3];
            }
            $toggle = !$toggle;
        }

        // Keepers: each keeps one distinct quarter and benches exactly once (not the keeper quarter)
        foreach ($keepers as $index => $kp) {
            $keeperQuarter = $index + 1; // mapped to Q1..Q4
            // choose a bench quarter different from keeperQuarter, spread them a bit
            $candidate = ($index % 4) + 2; // yields 2,3,4,5 -> mod 4 to 1..4
            $benchQuarter = (($candidate - 1) % 4) + 1;
            if ($benchQuarter === $keeperQuarter) {
                $benchQuarter = ($benchQuarter % 4) + 1; // next quarter
            }
            $benchPlan[$kp->id] = [$benchQuarter];
        }

        // Build attachments per quarter enforcing formation: 1 GK, 2 DEF, 1 MID, 2 ATT
        foreach (range(1, 4) as $q) {
            $attach = [];

            // First mark benches
            foreach ($players as $p) {
                $isBench = in_array($q, $benchPlan[$p->id] ?? [], true);
                if ($isBench) {
                    $attach[$p->id] = ['quarter' => $q, 'position_id' => null];
                }
            }

            // Determine available players this quarter (not benched)
            $available = $players->reject(function ($p) use ($benchPlan, $q) {
                return in_array($q, $benchPlan[$p->id] ?? [], true);
            })->values();

            // Select keeper for the quarter (if available)
            $selected = collect(); // player_id => role
            $keeperId = $keeperByQuarter[$q] ?? null;
            if ($keeperId) {
                // Only assign as keeper if not benched this quarter
                if (!isset($attach[$keeperId])) {
                    $selected[$keeperId] = 'keeper';
                    $attach[$keeperId] = ['quarter' => $q, 'position_id' => $repRolePos['keeper']];
                }
            }

            // Build role needs
            $needs = [
                'defender' => 2,
                'midfielder' => 1,
                'attacker' => 2,
            ];

            // Helper: pick players matching role preference first, considering weight balance
            $alreadySelectedIds = function () use ($selected) {
                return $selected->keys()->all();
            };

            $pickForRole = function (string $role) use (&$available, &$selected, $alreadySelectedIds, $positionIdToRole, $q, &$attach, $repRolePos, $calculateWeightBalance) {
                // Get candidates for this role
                $candidates = [];
                foreach ($available as $p) {
                    if (in_array($p->id, $alreadySelectedIds(), true)) continue;
                    $favRole = $positionIdToRole($p->position_id);
                    if ($favRole === $role) {
                        $candidates[] = $p;
                    }
                }

                if (empty($candidates)) return false;

                // Sort candidates by weight balance impact (prefer candidates that improve balance)
                usort($candidates, function ($a, $b) use ($selected, $calculateWeightBalance) {
                    $currentSelectedIds = $selected->keys()->all();

                    // Calculate balance score with each candidate added
                    $balanceWithA = $calculateWeightBalance(array_merge($currentSelectedIds, [$a->id]));
                    $balanceWithB = $calculateWeightBalance(array_merge($currentSelectedIds, [$b->id]));

                    // If balance scores are similar, prefer by weight (avoid clustering same weights)
                    if (abs($balanceWithA - $balanceWithB) < 0.1) {
                        return $a->weight <=> $b->weight;
                    }

                    return $balanceWithA <=> $balanceWithB;
                });

                // Pick the best candidate
                $p = $candidates[0];
                $selected[$p->id] = $role;
                // Assign position_id: player's favorite if within role, else representative
                $posId = $repRolePos[$role] ?? null;
                if ($positionIdToRole($p->position_id) === $role && $p->position_id) {
                    $posId = $p->position_id;
                }
                $attach[$p->id] = ['quarter' => $q, 'position_id' => $posId];
                return true;
            };

            // First pass: satisfy needs with matching favorites
            foreach (array_keys($needs) as $role) {
                while ($needs[$role] > 0 && $pickForRole($role)) {
                    $needs[$role]--;
                }
            }

            // Second pass: fill remaining slots from any available players not yet selected, considering weight balance
            foreach (array_keys($needs) as $role) {
                while ($needs[$role] > 0) {
                    $filled = false;

                    // Get all unselected available players and sort by weight balance impact
                    $remainingPlayers = $available->filter(function ($p) use ($selected) {
                        return !isset($selected[$p->id]);
                    })->values();

                    if ($remainingPlayers->isEmpty()) {
                        break; // No more players available
                    }

                    // Sort by weight balance impact
                    $remainingPlayers = $remainingPlayers->sort(function ($a, $b) use ($selected, $calculateWeightBalance) {
                        $currentSelectedIds = $selected->keys()->all();

                        $balanceWithA = $calculateWeightBalance(array_merge($currentSelectedIds, [$a->id]));
                        $balanceWithB = $calculateWeightBalance(array_merge($currentSelectedIds, [$b->id]));

                        // If balance scores are similar, prefer by weight diversity
                        if (abs($balanceWithA - $balanceWithB) < 0.1) {
                            return $a->weight <=> $b->weight;
                        }

                        return $balanceWithA <=> $balanceWithB;
                    });

                    $p = $remainingPlayers->first();
                    $selected[$p->id] = $role;
                    $posId = $repRolePos[$role] ?? null;
                    // if player's favorite fits this role use it, otherwise use representative
                    $favRole = $positionIdToRole($p->position_id);
                    if ($favRole === $role && $p->position_id) {
                        $posId = $p->position_id;
                    }
                    $attach[$p->id] = ['quarter' => $q, 'position_id' => $posId];
                    $needs[$role]--;
                    $filled = true;

                    if (!$filled) {
                        // No more players to fill this role; break to avoid infinite loop
                        break;
                    }
                }
            }

            // Any remaining available players not selected and not benched should get a generic outfield position
            foreach ($available as $p) {
                if (isset($selected[$p->id])) continue;
                if (isset($attach[$p->id])) continue; // benched already
                // Assign their favorite non-keeper, else fallback
                $posId = $p->position_id;
                if ($positionIdToRole($posId) === 'keeper') {
                    $posId = $fallbackOutfieldPositionId;
                }
                $attach[$p->id] = ['quarter' => $q, 'position_id' => $posId];
            }

            if (!empty($attach)) {
                $match->players()->attach($attach);
            }
        }

        return redirect()->route('football-matches.show', $match)->with('success', 'Wedstrijd aangemaakt en line-up automatisch gegenereerd.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FootballMatch $footballMatch): View
    {
        $footballMatch->load(['opponent']);
        // Fetch all pivot rows for this match and group by quarter to avoid de-duplication by player_id
        $rows = \App\Models\Player::query()
            ->select('players.id', 'players.name', 'players.weight', 'football_match_player.quarter', 'football_match_player.position_id')
            ->join('football_match_player', 'football_match_player.player_id', '=', 'players.id')
            ->where('football_match_player.football_match_id', $footballMatch->id)
            ->orderBy('players.name')
            ->get();

        $assignmentsByQuarter = [1 => collect(), 2 => collect(), 3 => collect(), 4 => collect()];
        foreach ($rows as $row) {
            $assignmentsByQuarter[(int)$row->quarter]->push($row);
        }

        // Map of position names by id for resolving per-quarter pivot position names
        $positionNames = Position::pluck('name', 'id');
        return view('football_matches.show', [
            'footballMatch' => $footballMatch,
            'positionNames' => $positionNames,
            'assignmentsByQuarter' => $assignmentsByQuarter,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FootballMatch $footballMatch): View
    {
        $opponents = Opponent::orderBy('name')->pluck('name', 'id');
        return view('football_matches.edit', compact('footballMatch', 'opponents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FootballMatch $footballMatch): RedirectResponse
    {
        $validated = $request->validate([
            'opponent_id' => ['required', 'exists:opponents,id'],
            'home' => ['required', 'boolean'],
            'goals_scores' => ['nullable', 'integer', 'min:0'],
            'goals_conceded' => ['nullable', 'integer', 'min:0'],
            'date' => ['required', 'date'],
        ]);
        $footballMatch->update($validated);
        return redirect()->route('football-matches.show', $footballMatch)->with('success', 'Wedstrijd bijgewerkt.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FootballMatch $footballMatch): RedirectResponse
    {
        $footballMatch->delete();
        return redirect()->route('football-matches.index')->with('success', 'Wedstrijd verwijderd.');
    }

    /**
     * Edit lineup (players to positions) per quarter for a match.
     */
    public function lineup(FootballMatch $footballMatch): View
    {
        $players = Player::orderBy('name')->get();
        $positions = Position::orderBy('name')->pluck('name', 'id');

        // Load existing assignments: [quarter][player_id] => position_id|null
        $existing = [];
        $footballMatch->load('players');
        foreach ($footballMatch->players as $p) {
            $q = (int)$p->pivot->quarter;
            $existing[$q][$p->id] = $p->pivot->position_id; // null means bench
        }

        return view('football_matches.lineup', [
            'footballMatch' => $footballMatch,
            'players' => $players,
            'positions' => $positions,
            'existing' => $existing,
        ]);
    }

    /**
     * Update lineup assignments.
     */
    public function lineupUpdate(Request $request, FootballMatch $footballMatch): RedirectResponse
    {
        // Expect structure: assignments[quarter][player_id] = position_id or "" for bench
        $data = $request->validate([
            'assignments' => ['array'],
            'assignments.*' => ['array'],
            'assignments.*.*' => ['nullable', 'integer', 'exists:positions,id'],
        ]);

        $assignments = $data['assignments'] ?? [];

        // We'll rebuild pivot entries for the provided quarters only.
        // Each quarter from 1..4 is valid. Absent players: no pivot row.
        // Bench: pivot row with position_id = null.
        $toAttach = [];

        foreach (range(1, 4) as $quarter) {
            $quarterAssignments = $assignments[$quarter] ?? [];

            // Detach all existing rows for this quarter; then attach the new set for that quarter
            $footballMatch->players()->wherePivot('quarter', $quarter)->detach();

            foreach ($quarterAssignments as $playerId => $posId) {
                // If value is null or empty string, treat as bench => attach with null position
                if ($posId === '' || is_null($posId)) {
                    $toAttach[$playerId] = ['quarter' => $quarter, 'position_id' => null];
                } else {
                    $toAttach[$playerId] = ['quarter' => $quarter, 'position_id' => (int)$posId];
                }
            }

            if (!empty($toAttach)) {
                $footballMatch->players()->attach($toAttach);
                $toAttach = [];
            }
        }

        return redirect()->route('football-matches.lineup', $footballMatch)->with('success', 'Line-up opgeslagen.');
    }
}
