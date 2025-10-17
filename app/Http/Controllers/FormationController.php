<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Rules\ValidFormation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FormationController extends Controller
{
    public function index(): View
    {
        $formations = Formation::orderBy('total_players')->paginate(15);
        return view('formations.index', compact('formations'));
    }

    public function create(): View
    {
        return view('formations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'total_players' => ['required', 'integer', 'min:1'],
            'lineup_formation' => ['required', 'string', 'max:255', new ValidFormation((int) $request->input('total_players'))],
        ]);

        $formation = Formation::create($validated);
        return redirect()->route('formations.show', $formation)->with('success', 'Formatie aangemaakt.');
    }

    public function show(Formation $formation): View
    {
        return view('formations.show', compact('formation'));
    }

    public function edit(Formation $formation): View
    {
        return view('formations.edit', compact('formation'));
    }

    public function update(Request $request, Formation $formation): RedirectResponse
    {
        $validated = $request->validate([
            'total_players' => ['required', 'integer', 'min:1'],
            'lineup_formation' => ['required', 'string', 'max:255', new ValidFormation((int) $request->input('total_players'))],
        ]);

        $formation->update($validated);

        return redirect()->route('formations.show', $formation)->with('success', 'Formatie bijgewerkt.');
    }

    public function destroy(Formation $formation): RedirectResponse
    {
        $formation->delete();
        return redirect()->route('formations.index')->with('success', 'Formatie verwijderd.');
    }
}
