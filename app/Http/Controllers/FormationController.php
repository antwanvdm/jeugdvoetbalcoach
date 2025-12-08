<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Rules\ValidFormation;
use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FormationController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Formation::class);

        $query = Formation::with('user')->orderBy('is_global')->orderBy('total_players');

        if (auth()->user()->isAdmin()){
            $query->where('is_global', true);
        }

        $formations = $query->paginate(15);

        return view('formations.index', compact('formations'));
    }

    public function create(): View
    {
        Gate::authorize('create', Formation::class);

        return view('formations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Formation::class);

        $validated = $request->validate([
            'total_players' => ['required', 'integer', 'min:1'],
            'lineup_formation' => ['required', 'string', 'max:255', new ValidFormation((int)$request->input('total_players'))],
            'is_global' => ['sometimes', 'boolean'],
        ]);

        // Only admins can create global formations
        if (isset($validated['is_global']) && $validated['is_global']) {
            if (!auth()->user()->isAdmin()) {
                abort(403, 'Only admins can create global formations.');
            }
            $validated['user_id'] = null; // Global formations don't belong to a specific user
            $validated['team_id'] = null; // Global formations don't belong to a specific team
        } else {
            $validated['user_id'] = auth()->id();
            $validated['team_id'] = session('current_team_id');
            $validated['is_global'] = false;
        }

        $formation = Formation::create($validated);
        return redirect()->route('formations.show', $formation)->with('success', 'Formatie aangemaakt.');
    }

    public function show(Formation $formation): View
    {
        Gate::authorize('view', $formation);

        list($defenders, $midfielders, $attackers) = explode('-', $formation->lineup_formation);

        return view('formations.show', compact('formation', 'defenders', 'midfielders', 'attackers'));
    }

    public function edit(Formation $formation): View
    {
        Gate::authorize('update', $formation);

        return view('formations.edit', compact('formation'));
    }

    public function update(Request $request, Formation $formation): RedirectResponse
    {
        Gate::authorize('update', $formation);

        $validated = $request->validate([
            'total_players' => ['required', 'integer', 'min:1'],
            'lineup_formation' => ['required', 'string', 'max:255', new ValidFormation((int)$request->input('total_players'))],
            'is_global' => ['sometimes', 'boolean'],
        ]);

        // Handle is_global updates (only admins can set/unset)
        if (isset($validated['is_global'])) {
            if (!auth()->user()->isAdmin()) {
                abort(403, 'Only admins can modify global formation status.');
            }

            if ($validated['is_global']) {
                $validated['user_id'] = null;
            } elseif ($formation->is_global && !$validated['is_global']) {
                // Converting from global to non-global
                $validated['user_id'] = auth()->id();
            }
        }

        $formation->update($validated);

        return redirect()->route('formations.show', $formation)->with('success', 'Formatie bijgewerkt.');
    }

    public function destroy(Formation $formation): RedirectResponse
    {
        Gate::authorize('delete', $formation);

        $formation->delete();
        return redirect()->route('formations.index')->with('success', 'Formatie verwijderd.');
    }
}
