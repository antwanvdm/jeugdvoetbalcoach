<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
        /**
     * Display a listing of the user's teams.
     */
    public function index()
    {
        $teams = auth()->user()->teams()
            ->withCount('users')
            ->get()
            ->map(function ($team) {
                $pivot = $team->pivot;
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'logo' => $team->logo,
                    'role' => $pivot->role,
                    'role_label' => $pivot->role === 1 ? 'Hoofdcoach' : 'Assistent',
                    'is_default' => $pivot->is_default,
                    'users_count' => $team->users_count,
                    'joined_at' => $pivot->joined_at,
                ];
            });

        $currentTeamId = session('current_team_id');

        return view('teams.index', compact('teams', 'currentTeamId'));
    }

    /**
     * Show the form for creating a new team.
     */
    public function create()
    {
        return view('teams.create');
    }

    /**
     * Store a newly created team.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    // Check if user is already member of a team with this name
                    $exists = auth()->user()->teams()
                        ->where('teams.name', $value)
                        ->exists();

                    if ($exists) {
                        $fail('Je bent al lid van een team met deze naam.');
                    }
                },
            ],
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'maps_location' => 'nullable|string|max:255',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        // Generate invite code for team
        $validated['invite_code'] = Str::random(64);

        $team = Team::create($validated);

        // Attach user as hoofdcoach
        // If this is user's first team, make it default
        $isFirstTeam = auth()->user()->teams()->count() === 0;

        auth()->user()->teams()->attach($team->id, [
            'role' => 1, // hoofdcoach
            'is_default' => $isFirstTeam,
            'joined_at' => now(),
        ]);

        // Switch to this team
        session(['current_team_id' => $team->id]);

        return redirect()->route('teams.index')
            ->with('success', 'Team succesvol aangemaakt!');
    }

    /**
     * Show the form for editing the specified team.
     */
    public function edit(Team $team)
    {
        \Gate::authorize('update', $team);

        return view('teams.edit', compact('team'));
    }

    /**
     * Update the specified team in storage.
     */
    public function update(Request $request, Team $team)
    {
        \Gate::authorize('update', $team);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($team) {
                    // Check if user is already member of a team with this name (excluding current team)
                    $exists = auth()->user()->teams()
                        ->where('teams.name', $value)
                        ->where('teams.id', '!=', $team->id)
                        ->exists();

                    if ($exists) {
                        $fail('Je bent al lid van een team met deze naam.');
                    }
                },
            ],
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'maps_location' => 'nullable|string|max:255',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($team->logo) {
                \Storage::disk('public')->delete($team->logo);
            }
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $team->update($validated);

        return redirect()->route('teams.index')
            ->with('success', 'Team succesvol bijgewerkt!');
    }

    /**
     * Switch to a different team.
     */
    public function switch(Team $team)
    {
        \Gate::authorize('view', $team);

        session(['current_team_id' => $team->id]);

        return redirect()->back()
            ->with('success', "Je werkt nu in team: {$team->name}");
    }

    /**
     * Set a team as the default team.
     */
    public function setDefault(Team $team)
    {
        \Gate::authorize('setDefault', $team);

        DB::transaction(function () use ($team) {
            // Set all teams to not default
            DB::table('team_user')
                ->where('user_id', auth()->id())
                ->update(['is_default' => false]);

            // Set this team as default
            DB::table('team_user')
                ->where('user_id', auth()->id())
                ->where('team_id', $team->id)
                ->update(['is_default' => true]);
        });

        return redirect()->route('teams.index')
            ->with('success', "{$team->name} is nu je standaard team.");
    }

    /**
     * Show the join team confirmation page.
     */
    public function showJoin(string $inviteCode)
    {
        $team = Team::where('invite_code', $inviteCode)->firstOrFail();

        // Store invite code in session
        session(['pending_team_invite' => $inviteCode]);

        // If user is not authenticated, redirect to register with team info
        if (!auth()->check()) {
            return redirect()->route('register')
                ->with('team_invite', [
                    'code' => $inviteCode,
                    'team_name' => $team->name,
                    'team_logo' => $team->logo,
                ]);
        }

        // Check if user is already a member
        if (auth()->user()->isMemberOf($team)) {
            return redirect()->route('teams.index')
                ->with('info', "Je bent al lid van {$team->name}.");
        }

        return view('teams.join', compact('team', 'inviteCode'));
    }

    /**
     * Join a team via invite code.
     */
    public function join(string $inviteCode)
    {
        $team = Team::where('invite_code', $inviteCode)->firstOrFail();

        // Check if user is already a member
        if (auth()->user()->isMemberOf($team)) {
            return redirect()->route('teams.index')
                ->with('info', "Je bent al lid van {$team->name}.");
        }

        // Add user as assistent
        auth()->user()->teams()->attach($team->id, [
            'role' => 2, // assistent
            'is_default' => false,
            'joined_at' => now(),
        ]);

        // Switch to this team
        session(['current_team_id' => $team->id]);

        return redirect()->route('teams.index')
            ->with('success', "Je bent nu lid van {$team->name}!");
    }

    /**
     * Regenerate invite code for a team (hoofdcoach only via 'update' policy).
     */
    public function regenerateInviteCode(Team $team)
    {
        \Gate::authorize('update', $team);

        $team->invite_code = Str::random(64);
        $team->save();

        return redirect()->route('teams.edit', $team)
            ->with('success', 'Nieuwe uitnodigingscode gegenereerd.');
    }

    /**
     * Leave a team.
     */
    public function leave(Team $team)
    {
        \Gate::authorize('leave', $team);

        DB::transaction(function () use ($team) {
            $isHoofdcoach = auth()->user()->isHoofdcoachOf($team);

            if ($isHoofdcoach) {
                // Promote first assistent to hoofdcoach
                $firstAssistent = DB::table('team_user')
                    ->where('team_id', $team->id)
                    ->where('role', 2)
                    ->orderBy('joined_at', 'asc')
                    ->first();

                if ($firstAssistent) {
                    DB::table('team_user')
                        ->where('team_id', $team->id)
                        ->where('user_id', $firstAssistent->user_id)
                        ->update(['role' => 1]);
                }
            }

            // Remove user from team
            auth()->user()->teams()->detach($team->id);

            // If this was the default team, set another team as default
            $wasDefault = DB::table('team_user')
                ->where('user_id', auth()->id())
                ->where('is_default', true)
                ->exists();

            if (!$wasDefault || auth()->user()->teams()->count() === 0) {
                return;
            }

            // Set oldest team as new default
            $oldestTeam = auth()->user()->teams()
                ->orderByPivot('joined_at', 'asc')
                ->first();

            if ($oldestTeam) {
                DB::table('team_user')
                    ->where('user_id', auth()->id())
                    ->where('team_id', $oldestTeam->id)
                    ->update(['is_default' => true]);

                session(['current_team_id' => $oldestTeam->id]);
            }
        });

        return redirect()->route('teams.index')
            ->with('success', 'Je hebt het team verlaten.');
    }

    /**
     * Remove the specified team.
     */
    public function destroy(Team $team)
    {
        \Gate::authorize('delete', $team);

        // Check if it's the user's last team
        if (auth()->user()->teams()->count() === 1) {
            return redirect()->route('teams.index')
                ->with('error', 'Je moet minimaal 1 team hebben. Je kunt je laatste team niet verwijderen.');
        }

        DB::transaction(function () use ($team) {
            $wasDefault = auth()->user()->teams()
                ->where('teams.id', $team->id)
                ->first()
                ->pivot
                ->is_default;

            $team->delete();

            // If this was default, set another team as default
            if ($wasDefault && auth()->user()->teams()->count() > 0) {
                $oldestTeam = auth()->user()->teams()
                    ->orderByPivot('joined_at', 'asc')
                    ->first();

                DB::table('team_user')
                    ->where('user_id', auth()->id())
                    ->where('team_id', $oldestTeam->id)
                    ->update(['is_default' => true]);

                session(['current_team_id' => $oldestTeam->id]);
            }
        });

        return redirect()->route('teams.index')->with('success', 'Team succesvol verwijderd.');
    }
}
