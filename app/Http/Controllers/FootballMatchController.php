<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Models\Opponent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FootballMatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $footballMatches = FootballMatch::with('opponent')->orderByDesc('date')->paginate(15);
        return view('football_matches.index', compact('footballMatches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $opponents = Opponent::orderBy('name')->pluck('name', 'id');
        return view('football_matches.create', compact('opponents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'opponent_id' => ['required','exists:opponents,id'],
            'home' => ['required','boolean'],
            'goals_scores' => ['nullable','integer','min:0'],
            'goals_conceded' => ['nullable','integer','min:0'],
            'date' => ['required','date'],
        ]);
        $match = FootballMatch::create($validated);
        return redirect()->route('football-matches.show', $match)->with('success', 'Match created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FootballMatch $footballMatch): View
    {
        $footballMatch->load('opponent');
        return view('football_matches.show', compact('footballMatch'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FootballMatch $footballMatch): View
    {
        $opponents = Opponent::orderBy('name')->pluck('name', 'id');
        return view('football_matches.edit', compact('footballMatch','opponents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FootballMatch $footballMatch): RedirectResponse
    {
        $validated = $request->validate([
            'opponent_id' => ['required','exists:opponents,id'],
            'home' => ['required','boolean'],
            'goals_scores' => ['nullable','integer','min:0'],
            'goals_conceded' => ['nullable','integer','min:0'],
            'date' => ['required','date'],
        ]);
        $footballMatch->update($validated);
        return redirect()->route('football-matches.show', $footballMatch)->with('success', 'Match updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FootballMatch $footballMatch): RedirectResponse
    {
        $footballMatch->delete();
        return redirect()->route('football-matches.index')->with('success', 'Match deleted.');
    }
}
