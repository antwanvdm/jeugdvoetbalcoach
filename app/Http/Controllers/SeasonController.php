<?php

namespace App\Http\Controllers;

use App\Models\Formation;
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

        return view('seasons.index', compact('seasons', 'onboardingInProgress'));
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

        return view('seasons.show', compact('season', 'matches', 'topScorers', 'topAssisters'));
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
        abort_if($season->share_token !== $shareToken, 404);

        $matches = $season->footballMatches()
            ->with('opponent')
            ->orderBy('date')
            ->get();

        $topScorers = $season->track_goals ? $season->topScorers(5) : collect();
        $topAssisters = $season->track_goals ? $season->topAssisters(5) : collect();

        return view('seasons.public', compact('season', 'matches', 'topScorers', 'topAssisters'));
    }
}
