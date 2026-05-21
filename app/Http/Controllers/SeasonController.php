<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\Player;
use App\Models\Season;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SeasonController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Season::class);

        $seasons = Season::orderByDesc('year')->orderByDesc('part')->paginate(15);
        $onboardingInProgress = !auth()->user()->hasTeamOnboardingCompleted();

        $yearsWithMultiple = Season::select('year')
            ->groupBy('year')
            ->havingRaw('COUNT(*) >= 2')
            ->pluck('year');

        return view('seasons.index', compact('seasons', 'onboardingInProgress', 'yearsWithMultiple'));
    }

    public function create(): View
    {
        Gate::authorize('create', Season::class);

        $formations = Formation::orderBy('total_players')->get()->mapWithKeys(fn($f) => [$f->id => $f->lineup_formation . ' (' . $f->total_players . ')']);
        return view('seasons.create', compact('formations'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Season::class);

        $data = $request->validate([
            'year' => ['required', 'integer'],
            'part' => ['required', 'integer', 'min:1'],
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after_or_equal:start'],
            'formation_id' => ['required', 'integer', 'exists:formations,id'],
        ]);

        $data['user_id'] = auth()->id();
        $data['team_id'] = session('current_team_id');

        // Automatically generate share token
        $data['share_token'] = Str::random(32);

        $season = Season::create($data);

        // Attach players from the most recent previous season (if any)
        $prev = Season::where('end', '<', $season->start)
            ->where('team_id', $data['team_id'])
            ->orderBy('end', 'desc')
            ->first();
        if ($prev) {
            $playerIds = \App\Models\Player::whereHas('seasons', function ($q) use ($prev) {
                $q->where('seasons.id', $prev->id);
            })->pluck('id')->toArray();

            if (!empty($playerIds)) {
                $season->players()->attach($playerIds);
            }
        }

        return redirect()->route('seasons.index')->with('success', 'Seizoen toegevoegd.');
    }

    public function show(Season $season): View
    {
        Gate::authorize('view', $season);

        $matches = $season->footballMatches()
            ->with('opponent')
            ->orderBy('date')
            ->get();

        $topScorers = $season->track_goals ? $season->topScorers(5) : collect();
        $topAssisters = $season->track_goals ? $season->topAssisters(5) : collect();

        // Get coaches for this team
        $coaches = $season->team->users()->get();

        return view('seasons.show', compact('season', 'matches', 'topScorers', 'topAssisters', 'coaches'));
    }

    public function edit(Season $season): View
    {
        Gate::authorize('update', $season);

        $formations = Formation::orderBy('total_players')->get()->mapWithKeys(fn($f) => [$f->id => $f->lineup_formation . ' (' . $f->total_players . ')']);
        return view('seasons.edit', compact('season', 'formations'));
    }

    public function update(Request $request, Season $season): RedirectResponse
    {
        Gate::authorize('update', $season);

        $data = $request->validate([
            'year' => ['required', 'integer'],
            'part' => ['required', 'integer', 'min:1'],
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after_or_equal:start'],
            'formation_id' => ['required', 'integer', 'exists:formations,id'],
            'track_goals' => ['boolean'],
        ]);

        $season->update($data);

        return redirect()->route('seasons.index')->with('success', 'Seizoen bijgewerkt.');
    }

    public function destroy(Season $season): RedirectResponse
    {
        Gate::authorize('delete', $season);

        $season->delete();
        return redirect()->route('seasons.index')->with('success', 'Seizoen verwijderd.');
    }

    public function showYear(int $year): View
    {
        Gate::authorize('viewAny', Season::class);

        $seasons = Season::where('year', $year)
            ->with(['footballMatches.opponent', 'footballMatches.goals', 'footballMatches.season'])
            ->get();

        abort_if($seasons->count() < 2, 404);

        $yearShareToken = self::computeYearShareToken($seasons);
        $dateFrom = $seasons->sortBy('start')->first()->start->format('d-m-Y');
        $dateTo   = $seasons->sortByDesc('end')->first()->end->format('d-m-Y');

        [$stats, $allMatches, $topScorers, $topAssisters, $trackGoals] = $this->aggregateYearData($seasons, session('current_team_id'));
        $coaches = $seasons->first()->team->users()->get();
        $isPublic = false;

        return view('seasons.year', compact('year', 'seasons', 'stats', 'allMatches', 'topScorers', 'topAssisters', 'coaches', 'trackGoals', 'yearShareToken', 'dateFrom', 'dateTo', 'isPublic'));
    }

    public function showYearPublic(int $year, string $shareToken): View
    {
        $allYearSeasons = Season::withoutGlobalScope('team')
            ->where('year', $year)
            ->with(['footballMatches.opponent', 'footballMatches.goals', 'footballMatches.season'])
            ->get();

        $seasons = null;
        foreach ($allYearSeasons->groupBy('team_id') as $teamSeasons) {
            if ($teamSeasons->count() < 2) continue;
            if (hash_equals(self::computeYearShareToken($teamSeasons), $shareToken)) {
                $seasons = $teamSeasons->values();
                break;
            }
        }
        abort_if($seasons === null, 404);

        $yearShareToken = $shareToken;
        $dateFrom = $seasons->sortBy('start')->first()->start->format('d-m-Y');
        $dateTo   = $seasons->sortByDesc('end')->first()->end->format('d-m-Y');

        [$stats, $allMatches, $topScorers, $topAssisters, $trackGoals] = $this->aggregateYearData($seasons, $seasons->first()->team_id);
        $coaches = $seasons->first()->team->users()->get();
        $isPublic = true;

        return view('seasons.year', compact('year', 'seasons', 'stats', 'allMatches', 'topScorers', 'topAssisters', 'coaches', 'trackGoals', 'yearShareToken', 'dateFrom', 'dateTo', 'isPublic'));
    }

    private function aggregateYearData($seasons, int $teamId): array
    {
        $allMatches = $seasons->flatMap(fn($s) => $s->footballMatches)->sortBy('date');
        $matchesWithResult = $allMatches->filter(fn($m) => !is_null($m->goals_scored));

        $stats = [
            'total'         => $matchesWithResult->count(),
            'wins'          => $matchesWithResult->filter(fn($m) => $m->result === 'W')->count(),
            'draws'         => $matchesWithResult->filter(fn($m) => $m->result === 'D')->count(),
            'losses'        => $matchesWithResult->filter(fn($m) => $m->result === 'L')->count(),
            'goals_for'     => $matchesWithResult->sum('goals_scored'),
            'goals_against' => $matchesWithResult->sum('goals_conceded'),
            'goal_diff'     => $matchesWithResult->sum('goals_scored') - $matchesWithResult->sum('goals_conceded'),
        ];

        $trackGoals = $seasons->contains(fn($s) => $s->track_goals);
        $seasonIds  = $seasons->pluck('id');

        $topScorers = $trackGoals ? Player::query()
            ->select('players.*')
            ->selectRaw('COUNT(match_goals.id) as goals_count')
            ->join('match_goals', 'players.id', '=', 'match_goals.player_id')
            ->join('football_matches', 'match_goals.football_match_id', '=', 'football_matches.id')
            ->whereIn('football_matches.season_id', $seasonIds)
            ->where('football_matches.team_id', $teamId)
            ->groupBy('players.id')
            ->orderByDesc('goals_count')
            ->limit(5)
            ->get() : collect();

        $topAssisters = $trackGoals ? Player::query()
            ->select('players.*')
            ->selectRaw('COUNT(match_goals.id) as assists_count')
            ->join('match_goals', 'players.id', '=', 'match_goals.assist_player_id')
            ->join('football_matches', 'match_goals.football_match_id', '=', 'football_matches.id')
            ->whereIn('football_matches.season_id', $seasonIds)
            ->where('football_matches.team_id', $teamId)
            ->groupBy('players.id')
            ->orderByDesc('assists_count')
            ->limit(5)
            ->get() : collect();

        return [$stats, $allMatches, $topScorers, $topAssisters, $trackGoals];
    }

    private static function computeYearShareToken($seasons): string
    {
        return substr(
            hash('sha256', $seasons->sortBy('id')->map(fn($s) => $s->id . ':' . ($s->share_token ?? ''))->join('|')),
            0, 64
        );
    }

    /**
     * Regenerate the share token for a season.
     */
    public function regenerateShareToken(Season $season): RedirectResponse
    {
        Gate::authorize('update', $season);

        $season->share_token = Str::random(32);
        $season->save();

        return redirect()->route('seasons.edit', $season)->with('success', 'Nieuwe deellink gegenereerd.');
    }

    /**
     * Public view for season (parents).
     */
    public function showPublic(Season $season, string $shareToken): View
    {
        abort_if(!hash_equals($season->share_token ?? '', $shareToken), 404);

        $matches = $season->footballMatches()
            ->with('opponent')
            ->orderBy('date')
            ->get();

        $topScorers = $season->track_goals ? $season->topScorers(5) : collect();
        $topAssisters = $season->track_goals ? $season->topAssisters(5) : collect();

        // Get coaches for this team
        $coaches = $season->team->users()->get();

        return view('seasons.show', compact('season', 'matches', 'topScorers', 'topAssisters', 'coaches'));
    }
}
