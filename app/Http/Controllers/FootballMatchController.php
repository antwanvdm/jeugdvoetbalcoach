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

        // Historical keeper counts per player
        $keeperCounts = Player::query()
            ->withCount([
                'footballMatches as keeper_count' => fn($q) =>
                $q->where('football_match_player.position_id', $keeperPositionId)
            ])
            ->pluck('keeper_count', 'id');

        // Choose 4 keepers with the least historical keeper counts
        $keepers = $players->sortBy(fn($p) => [(int)$keeperCounts->get($p->id, 0), $p->name])->take(4)->values();
        $keeperByQuarter = $keepers->mapWithKeys(fn($keeper, $index) => [$index + 1 => $keeper->id])->toArray();

        // Bench plan per player per quarter
        $benchPlan = []; // [player_id => [quarters...]]

        // Non-keepers: bench exactly 2 times, not consecutive -> alternate Q1+Q3 vs Q2+Q4
        $toggle = false;
        foreach ($players as $p) {
            $isSelectedKeeper = in_array($p->id, $keepers->pluck('id')->all(), true);
            if ($isSelectedKeeper) continue; // handle below
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

            // Helper: pick players matching role preference first
            $alreadySelectedIds = function () use ($selected) {
                return $selected->keys()->all();
            };

            $pickForRole = function (string $role) use (&$available, &$selected, $alreadySelectedIds, $positionIdToRole, $q, &$attach, $repRolePos) {
                foreach ($available as $p) {
                    if (in_array($p->id, $alreadySelectedIds(), true)) continue;
                    $favRole = $positionIdToRole($p->position_id);
                    if ($favRole === $role) {
                        $selected[$p->id] = $role;
                        // Assign position_id: player's favorite if within role, else representative
                        $posId = $repRolePos[$role] ?? null;
                        if ($favRole === $role && $p->position_id) {
                            $posId = $p->position_id;
                        }
                        $attach[$p->id] = ['quarter' => $q, 'position_id' => $posId];
                        return true;
                    }
                }
                return false;
            };

            // First pass: satisfy needs with matching favorites
            foreach (array_keys($needs) as $role) {
                while ($needs[$role] > 0 && $pickForRole($role)) {
                    $needs[$role]--;
                }
            }

            // Second pass: fill remaining slots from any available players not yet selected
            foreach (array_keys($needs) as $role) {
                while ($needs[$role] > 0) {
                    $filled = false;
                    foreach ($available as $p) {
                        if (isset($selected[$p->id])) continue;
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
                        break;
                    }
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
            ->select('players.id', 'players.name', 'football_match_player.quarter', 'football_match_player.position_id')
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
