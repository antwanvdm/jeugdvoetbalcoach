<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Models\Opponent;
use App\Models\Player;
use App\Models\Position;
use App\Models\Season;
use App\Services\LineupGeneratorService;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FootballMatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', FootballMatch::class);

        $seasons = Season::orderByDesc('year')->orderByDesc('part')->get();

        // Check if there are any seasons
        if ($seasons->isEmpty()) {
            return view('football_matches.no-season');
        }

        $activeSeason = Season::getCurrent($seasons);

        $seasonId = $request->query('season_id') ?? ($activeSeason?->id ?? null);
        $matchesQuery = FootballMatch::with('opponent')->orderByDesc('date');
        if ($seasonId !== 'all') {
            $matchesQuery->where('season_id', $seasonId);
        }
        $footballMatches = $matchesQuery->paginate(15)->withQueryString();

        return view('football_matches.index', compact('footballMatches', 'seasons', 'activeSeason', 'seasonId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View|RedirectResponse
    {
        Gate::authorize('create', FootballMatch::class);

        // Require at least one player in the current team before creating a match
        if (!Player::exists()) {
            return view('football_matches.no-players');
        }

        // Get season_id from request (required)
        $seasonId = $request->query('season_id') ?? old('season_id');

        // If no season provided, redirect to index
        if (!$seasonId) {
            $currentSeason = Season::getCurrent();
            return redirect()->route('football-matches.create', ['season_id' => $currentSeason->id])->with('error', 'Selecteer eerst een seizoen.');
        }

        // Validate that the season belongs to the current team
        $season = Season::where('id', $seasonId)
            ->where('team_id', session('current_team_id'))
            ->first();

        if (!$season) {
            abort(403, 'Dit seizoen hoort niet tot jouw team.');
        }

        $opponents = Opponent::orderBy('name')->pluck('name', 'id');

        // Get players for the selected season
        $players = Player::whereHas('seasons', function ($q) use ($seasonId) {
            $q->where('seasons.id', $seasonId);
        })->orderBy('name')->get();

        return view('football_matches.create', compact('opponents', 'season', 'players'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, LineupGeneratorService $lineupGenerator): RedirectResponse
    {
        Gate::authorize('create', FootballMatch::class);

        $validated = $request->validate([
            'opponent_id' => ['required', 'exists:opponents,id'],
            'home' => ['required', 'boolean'],
            'goals_scored' => ['nullable', 'integer', 'min:0'],
            'goals_conceded' => ['nullable', 'integer', 'min:0'],
            'date' => ['required', 'date'],
            'available_players' => ['nullable', 'array'],
            'available_players.*' => ['exists:players,id'],
        ]);

        // Validate season_id and check it belongs to current team
        $request->validate(['season_id' => ['required', 'exists:seasons,id']]);

        $season = Season::where('id', $request->input('season_id'))
            ->where('team_id', session('current_team_id'))
            ->first();

        if (!$season) {
            abort(403, 'Dit seizoen hoort niet tot jouw team.');
        }

        $validated['season_id'] = $season->id;
        $validated['user_id'] = auth()->id();
        $validated['team_id'] = session('current_team_id');
        $validated['share_token'] = Str::random(32);
        $match = FootballMatch::create($validated);

        // Get selected (available) players - if none selected, use all players from season
        $availablePlayerIds = $request->input('available_players', []);

        // Generate lineup using the service with available players
        $lineupGenerator->generateLineup($match, $availablePlayerIds);

        return redirect()->route('football-matches.show', $match)
            ->with('success', 'Wedstrijd aangemaakt en line-up automatisch gegenereerd.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FootballMatch $footballMatch): View
    {
        Gate::authorize('view', $footballMatch);

        $data = $this->getMatchViewData($footballMatch);

        return view('football_matches.show', $data);
    }

    /**
     * Display the match details publicly via share token (for parents).
     */
    public function showPublic(FootballMatch $footballMatch, string $shareToken): View
    {
        // Validate share token - use withoutGlobalScope to bypass team scope
        $match = FootballMatch::withoutGlobalScope('team')
            ->where('id', $footballMatch->id)
            ->where('share_token', $shareToken)
            ->firstOrFail();

        $data = $this->getMatchViewData($match);
        $data['isPublicView'] = true;

        return view('football_matches.show', $data);
    }

    /**
     * Get shared view data for a football match.
     */
    private function getMatchViewData(FootballMatch $footballMatch): array
    {
        $footballMatch->load(['opponent']);

        // Fetch all pivot rows for this match and group by quarter to avoid de-duplication by player_id
        $rows = Player::query()
            ->select('players.id', 'players.name', 'players.weight', 'football_match_player.quarter', 'football_match_player.position_id')
            ->join('football_match_player', 'football_match_player.player_id', '=', 'players.id')
            ->where('football_match_player.football_match_id', $footballMatch->id)
            ->whereHas('seasons', function ($q) use ($footballMatch) {
                $q->where('seasons.id', $footballMatch->season_id);
            })
            ->orderBy('players.name')
            ->get();

        $assignmentsByQuarter = [1 => collect(), 2 => collect(), 3 => collect(), 4 => collect()];
        foreach ($rows as $row) {
            $assignmentsByQuarter[(int)$row->quarter]->push($row);
        }

        // Map of position names by id for resolving per-quarter pivot position names
        $positionNames = Position::pluck('name', 'id');

        // Get all players with their quarter counts for this match (only actual playing time, not bench)
        $allPlayers = Player::orderBy('name')->whereHas('seasons', function ($q) use ($footballMatch) {
            $q->where('seasons.id', $footballMatch->season_id);
        })->get();
        $playersWithQuarters = [];

        foreach ($allPlayers as $player) {
            // Only count quarters where position_id is not null (actually playing, not on bench)
            $quartersPlayed = $rows->where('id', $player->id)->whereNotNull('position_id')->count();
            $playersWithQuarters[] = [
                'player' => $player,
                'quarters_played' => $quartersPlayed
            ];
        }

        // Sort: players with quarters played first (by quarters desc, then name), then absent players by name
        usort($playersWithQuarters, function ($a, $b) {
            if ($a['quarters_played'] == 0 && $b['quarters_played'] == 0) {
                return strcmp($a['player']->name, $b['player']->name);
            }
            if ($a['quarters_played'] == 0) return 1;
            if ($b['quarters_played'] == 0) return -1;

            $quartersDiff = $b['quarters_played'] - $a['quarters_played'];
            if ($quartersDiff != 0) return $quartersDiff;

            return strcmp($a['player']->name, $b['player']->name);
        });

        return [
            'footballMatch' => $footballMatch,
            'positionNames' => $positionNames,
            'assignmentsByQuarter' => $assignmentsByQuarter,
            'playersWithQuarters' => $playersWithQuarters,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FootballMatch $footballMatch): View
    {
        Gate::authorize('update', $footballMatch);

        $opponents = Opponent::orderBy('name')->pluck('name', 'id');
        $seasonsMapped = Season::orderByDesc('year')->orderByDesc('part')
            ->get()
            ->mapWithKeys(fn($s) => [$s->id => $s->year . '-' . $s->part]);

        return view('football_matches.edit', compact('footballMatch', 'opponents', 'seasonsMapped'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FootballMatch $footballMatch): RedirectResponse
    {
        Gate::authorize('update', $footballMatch);

        $validated = $request->validate([
            'opponent_id' => ['required', 'exists:opponents,id'],
            'home' => ['required', 'boolean'],
            'goals_scored' => ['nullable', 'integer', 'min:0'],
            'goals_conceded' => ['nullable', 'integer', 'min:0'],
            'date' => ['required', 'date'],
        ]);
        $request->validate(['season_id' => ['nullable', 'exists:seasons,id']]);
        $validated = array_merge($validated, $request->only('season_id'));

        $footballMatch->update($validated);
        return redirect()->route('football-matches.show', $footballMatch)->with('success', 'Wedstrijd bijgewerkt.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FootballMatch $footballMatch): RedirectResponse
    {
        Gate::authorize('delete', $footballMatch);

        $footballMatch->delete();
        return redirect()->route('football-matches.index')->with('success', 'Wedstrijd verwijderd.');
    }

    /**
     * Edit lineup (players to positions) per quarter for a match.
     */
    public function lineup(FootballMatch $footballMatch): View
    {
        Gate::authorize('update', $footballMatch);

        $players = Player::whereHas('seasons', function ($q) use ($footballMatch) {
            $q->where('seasons.id', $footballMatch->season_id);
        })->orderBy('name')->get();
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
        Gate::authorize('update', $footballMatch);

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

        return redirect()->route('football-matches.show', $footballMatch)->with('success', 'Line-up opgeslagen.');
    }
}
