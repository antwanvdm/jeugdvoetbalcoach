<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeasonController extends Controller
{
    public function index(): View
    {
        $seasons = Season::orderByDesc('year')->orderByDesc('part')->paginate(15);
        return view('seasons.index', compact('seasons'));
    }

    public function create(): View
    {
        $formations = Formation::orderBy('total_players')->get()->mapWithKeys(fn($f) => [$f->id => $f->lineup_formation . ' (' . $f->total_players . ')']);
        return view('seasons.create', compact('formations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer'],
            'part' => ['required', 'integer', 'min:1'],
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after_or_equal:start'],
            'formation_id' => ['required', 'integer', 'exists:formations,id'],
        ]);

        $season = Season::create($data);

        // Attach players from the most recent previous season (if any)
        $prev = Season::where('end', '<', $season->start)->orderBy('end', 'desc')->first();
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
        return view('seasons.show', compact('season'));
    }

    public function edit(Season $season): View
    {
        $formations = Formation::orderBy('total_players')->get()->mapWithKeys(fn($f) => [$f->id => $f->lineup_formation . ' (' . $f->total_players . ')']);
        return view('seasons.edit', compact('season', 'formations'));
    }

    public function update(Request $request, Season $season): RedirectResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer'],
            'part' => ['required', 'integer', 'min:1'],
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after_or_equal:start'],
            'formation_id' => ['required', 'integer', 'exists:formations,id'],
        ]);

        $season->update($data);

        return redirect()->route('seasons.index')->with('success', 'Seizoen bijgewerkt.');
    }

    public function destroy(Season $season): RedirectResponse
    {
        $season->delete();
        return redirect()->route('seasons.index')->with('success', 'Seizoen verwijderd.');
    }
}
