<?php

namespace App\Http\Controllers;

use App\Models\Opponent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpponentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $opponents = Opponent::orderBy('name')->paginate(15);
        return view('opponents.index', compact('opponents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('opponents.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $validated['user_id'] = auth()->id();
        $opponent = Opponent::create($validated);
        return redirect()->route('opponents.show', $opponent)->with('success', 'Tegenstander aangemaakt.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Opponent $opponent): View
    {
        return view('opponents.show', compact('opponent'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Opponent $opponent): View
    {
        return view('opponents.edit', compact('opponent'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Opponent $opponent): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);
        $opponent->update($validated);
        return redirect()->route('opponents.show', $opponent)->with('success', 'Tegenstander bijgewerkt.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Opponent $opponent): RedirectResponse
    {
        $opponent->delete();
        return redirect()->route('opponents.index')->with('success', 'Opponent deleted.');
    }
}
