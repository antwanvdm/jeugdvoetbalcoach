<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $positions = Position::orderBy('name')->paginate(15);
        return view('positions.index', compact('positions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('positions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
        ]);
        $position = Position::create($validated);
        return redirect()->route('positions.show', $position)->with('success', 'Position created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Position $position): View
    {
        return view('positions.show', compact('position'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Position $position): View
    {
        return view('positions.edit', compact('position'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Position $position): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
        ]);
        $position->update($validated);
        return redirect()->route('positions.show', $position)->with('success', 'Position updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position): RedirectResponse
    {
        $position->delete();
        return redirect()->route('positions.index')->with('success', 'Position deleted.');
    }
}
