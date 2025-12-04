<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Position;
use App\Models\Season;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlayerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Player::class);

        $seasons = Season::orderByDesc('year')->orderByDesc('part')->get();

        // Check if there are any seasons
        if ($seasons->isEmpty()) {
            return view('players.no-season');
        }

        $activeSeason = Season::getCurrent($seasons);
        $seasonId = $request->query('season_id') ?? ($activeSeason?->id ?? null);

        $players = Player::with('position')
            ->withCount([
                'footballMatches as keeper_count' => function ($query) use ($seasonId) {
                    $query->where('football_match_player.position_id', 1);
                    $query->where('season_id', $seasonId);
                }
            ])
            ->whereHas('seasons', function ($q) use ($seasonId) {
                $q->where('seasons.id', $seasonId);
            })
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        $onboardingInProgress = !auth()->user()->hasTeamOnboardingCompleted();

        return view('players.index', compact('players', 'seasons', 'activeSeason', 'seasonId', 'onboardingInProgress'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', Player::class);

        $positions = Position::orderBy('name')->pluck('name', 'id');
        $seasonsCollection = Season::orderByDesc('year')->orderByDesc('part')->get();
        $seasons = $seasonsCollection->mapWithKeys(fn($s) => [$s->id => $s->year . '-' . $s->part]);
        $currentSeason = Season::getCurrent($seasonsCollection);

        return view('players.create', compact('positions', 'seasons', 'currentSeason'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Player::class);

        $validated = $request->validate([
            'players' => ['required', 'array', 'min:1'],
            'players.*.name' => ['required', 'string', 'max:255'],
            'players.*.position_id' => ['required', 'exists:positions,id'],
            'players.*.weight' => ['required', 'numeric', 'min:1', 'max:2'],
            'seasons' => ['required', 'array', 'min:1'],
            'seasons.*' => ['integer', 'exists:seasons,id'],
        ]);

        $userId = auth()->id();
        $teamId = session('current_team_id');
        $seasonIds = array_filter((array)$request->input('seasons'));

        $createdCount = 0;
        foreach ($validated['players'] as $i => $playerData) {
            // Checkbox: if not set, fallback to 1 (hidden input), if checked, value is 2
            if (isset($request->players[$i]['weight']) && $request->players[$i]['weight'] == '2') {
                $playerData['weight'] = 2;
            } else {
                $playerData['weight'] = 1;
            }
            $playerData['user_id'] = $userId;
            $playerData['team_id'] = $teamId;
            $player = Player::create($playerData);
            $player->seasons()->sync($seasonIds);
            $createdCount++;
        }

        $message = $createdCount === 1
            ? 'Speler aangemaakt.'
            : "{$createdCount} spelers aangemaakt.";

        return redirect()->route('players.index')->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(Player $player): View
    {
        Gate::authorize('view', $player);

        $player->load('position');
        return view('players.show', compact('player'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Player $player): View
    {
        Gate::authorize('update', $player);

        $positions = Position::orderBy('name')->pluck('name', 'id');
        $seasons = Season::orderByDesc('year')->orderByDesc('part')->get()->mapWithKeys(fn($s) => [$s->id => $s->year . '-' . $s->part]);
        return view('players.edit', compact('player', 'positions', 'seasons'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Player $player): RedirectResponse
    {
        Gate::authorize('update', $player);


        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position_id' => ['required', 'exists:positions,id'],
            'weight' => ['nullable'],
        ]);

        // Checkbox: if not set, fallback to 1 (hidden input), if checked, value is 2
        $validated['weight'] = $request->input('weight', 1) == '2' ? 2 : 1;

        $player->update($validated);

        // sync seasons if provided
        if ($request->has('seasons')) {
            $seasonIds = array_filter((array)$request->input('seasons'));
            $player->seasons()->sync($seasonIds);
        }

        return redirect()->route('players.index', $player)->with('success', 'Speler bijgewerkt.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Player $player): RedirectResponse
    {
        Gate::authorize('delete', $player);

        $player->delete();
        return redirect()->route('players.index')->with('success', 'Speler verwijderd.');
    }
}
