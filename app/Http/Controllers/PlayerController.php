<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlayerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $players = Player::with('position')
            ->withCount([
                'footballMatches as keeper_count' => function ($query) {
                    $query->where('football_match_player.position_id', 1);
                }
            ])
            ->orderBy('name')
            ->paginate(15);

        return view('players.index', compact('players'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $positions = Position::orderBy('name')->pluck('name', 'id');
        return view('players.create', compact('positions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position_id' => ['required', 'exists:positions,id'],
            'weight' => ['required', 'numeric'],
        ]);

        $player = Player::create($validated);

        return redirect()->route('players.show', $player)->with('success', 'Speler aangemaakt.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Player $player): View
    {
        $player->load('position');
        return view('players.show', compact('player'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Player $player): View
    {
        $positions = Position::orderBy('name')->pluck('name', 'id');
        return view('players.edit', compact('player', 'positions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Player $player): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position_id' => ['required', 'exists:positions,id'],
            'weight' => ['required', 'numeric'],
        ]);

        $player->update($validated);

        return redirect()->route('players.show', $player)->with('success', 'Speler bijgewerkt.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Player $player): RedirectResponse
    {
        $player->delete();
        return redirect()->route('players.index')->with('success', 'Player deleted.');
    }
}
