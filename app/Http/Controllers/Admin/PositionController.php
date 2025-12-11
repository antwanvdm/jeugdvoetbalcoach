<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', Position::class);

        $positions = Position::orderBy('name')->paginate(15);
        return view('positions.index', compact('positions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('create', Position::class);

        return view('positions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Position::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        $position = Position::create($validated);
        return redirect()->route('positions.show', $position)->with('success', 'Positie aangemaakt.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Position $position): View
    {
        Gate::authorize('view', $position);

        return view('positions.show', compact('position'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Position $position): View
    {
        Gate::authorize('update', $position);

        return view('positions.edit', compact('position'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Position $position): RedirectResponse
    {
        Gate::authorize('update', $position);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        $position->update($validated);
        return redirect()->route('positions.show', $position)->with('success', 'Positie bijgewerkt.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position): RedirectResponse
    {
        Gate::authorize('delete', $position);

        $position->delete();
        return redirect()->route('positions.index')->with('success', 'Positie verwijder.');
    }
}
