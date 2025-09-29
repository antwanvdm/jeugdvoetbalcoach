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
            'opponent_id' => ['required','exists:opponents,id'],
            'home' => ['required','boolean'],
            'goals_scores' => ['nullable','integer','min:0'],
            'goals_conceded' => ['nullable','integer','min:0'],
            'date' => ['required','date'],
        ]);
        $match = FootballMatch::create($validated);
        return redirect()->route('football-matches.show', $match)->with('success', 'Wedstrijd aangemaakt.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FootballMatch $footballMatch): View
    {
        $footballMatch->load(['opponent']);
        // Fetch all pivot rows for this match and group by quarter to avoid de-duplication by player_id
        $rows = \App\Models\Player::query()
            ->select('players.id','players.name','football_match_player.quarter','football_match_player.position_id')
            ->join('football_match_player', 'football_match_player.player_id', '=', 'players.id')
            ->where('football_match_player.football_match_id', $footballMatch->id)
            ->orderBy('players.name')
            ->get();

        $assignmentsByQuarter = [1 => collect(), 2 => collect(), 3 => collect(), 4 => collect()];
        foreach ($rows as $row) {
            $assignmentsByQuarter[(int)$row->quarter]->push($row);
        }

        // Map of position names by id for resolving per-quarter pivot position names
        $positionNames = Position::pluck('name','id');
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
        return view('football_matches.edit', compact('footballMatch','opponents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FootballMatch $footballMatch): RedirectResponse
    {
        $validated = $request->validate([
            'opponent_id' => ['required','exists:opponents,id'],
            'home' => ['required','boolean'],
            'goals_scores' => ['nullable','integer','min:0'],
            'goals_conceded' => ['nullable','integer','min:0'],
            'date' => ['required','date'],
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
        $positions = Position::orderBy('name')->pluck('name','id');

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
            'assignments.*.*' => ['nullable','integer','exists:positions,id'],
        ]);

        $assignments = $data['assignments'] ?? [];

        // We'll rebuild pivot entries for the provided quarters only.
        // Each quarter from 1..4 is valid. Absent players: no pivot row.
        // Bench: pivot row with position_id = null.
        $toAttach = [];

        foreach (range(1,4) as $quarter) {
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
