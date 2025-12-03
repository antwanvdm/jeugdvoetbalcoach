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
            ->with(['opponent'])
            ->withCount('users')
            ->get()
            ->map(function ($team) {
                $pivot = $team->pivot;
                return [
                    'id' => $team->id,
                    'name' => $team->opponent?->name,
                    'logo' => $team->opponent?->logo,
                    'invite_code' => $team->invite_code,
                    'role' => $pivot->role,
                    'role_label' => 'Coach', //$pivot->role === 1 ? 'Hoofdcoach' : 'Assistent',
                    'is_default' => $pivot->is_default,
                    'label' => $pivot->label,
                    'users_count' => $team->users_count,
                    'joined_at' => $pivot->joined_at,
                    'can_leave' => \Gate::allows('leave', $team),
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
     * Display the specified team with its members.
     */
    public function show(Team $team)
    {
        \Gate::authorize('view', $team);

        $members = $team->users()
            ->withPivot('role', 'is_default', 'joined_at', 'label')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->pivot->role,
                    'role_label' => 'Coach', //$user->pivot->role === 1 ? 'Hoofdcoach' : 'Assistent',
                    'label' => $user->pivot->label,
                    'joined_at' => \Carbon\Carbon::parse($user->pivot->joined_at),
                ];
            });

        return view('teams.show', compact('team', 'members'));
    }

    /**
     * Store a newly created team.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'opponent_id' => ['required', 'integer', 'exists:opponents,id', function ($attribute, $value, $fail) {
                $exists = auth()->user()->teams()->where('teams.opponent_id', $value)->exists();
                if ($exists) {
                    $fail('Je bent al lid van een team voor deze club.');
                }
            }],
        ]);

        $team = Team::create([
            'opponent_id' => $validated['opponent_id'],
            'invite_code' => Str::random(64),
        ]);

        // Attach user as hoofdcoach
        // If this is user's first team, make it default
        $isFirstTeam = auth()->user()->teams()->count() === 0;

        auth()->user()->teams()->attach($team->id, [
            'role' => 1, // hoofdcoach
            'is_default' => $isFirstTeam,
            'joined_at' => now(),
            'label' => $request->string('label')->toString() ?: null,
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
            'opponent_id' => ['required', 'integer', 'exists:opponents,id', function ($attribute, $value, $fail) use ($team) {
                $exists = auth()->user()->teams()
                    ->where('teams.opponent_id', $value)
                    ->where('teams.id', '!=', $team->id)
                    ->exists();
                if ($exists) {
                    $fail('Je bent al lid van een team voor deze club.');
                }
            }],
        ]);

        $team->update(['opponent_id' => $validated['opponent_id']]);

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

        return redirect()->route('dashboard')->with('success', "Je werkt nu in team: {$team->opponent?->name}");
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
            ->with('success', "{$team->opponent?->name} is nu je standaard team.");
    }

    /**
     * Show the join team confirmation page.
     */
    public function showJoin(string $inviteCode)
    {
        $team = Team::where('invite_code', $inviteCode)->firstOrFail();

        // Store invite code in session for post-registration/verification flow
        session(['pending_team_invite' => $inviteCode]);

        // If user is not authenticated, redirect to register with team info
        if (!auth()->check()) {
            return redirect()->route('register')
                ->with('team_invite', [
                    'code' => $inviteCode,
                    'team_name' => $team->opponent?->name,
                    'team_logo' => $team->opponent?->logo,
                ]);
        }

        // Check if user is already a member
        if (auth()->user()->isMemberOf($team)) {
            return redirect()->route('teams.index')
                ->with('info', "Je bent al lid van {$team->opponent?->name}.");
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
        // Set as default if this is their first/only team
        $isFirstTeam = auth()->user()->teams()->count() === 0;

        auth()->user()->teams()->attach($team->id, [
            'role' => 2, // assistent
            'is_default' => $isFirstTeam,
            'joined_at' => now(),
            'label' => request()->string('label')->toString() ?: null,
        ]);

        // Switch to this team
        session(['current_team_id' => $team->id]);

        return redirect()->route('teams.index')
            ->with('success', "Je bent nu lid van {$team->opponent?->name}!");
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
            $isHoofdcoach = auth()->user()->isHeadCoach($team);

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

    /**
     * Update the current user's label on a team (pivot: team_user.label).
     */
    public function updateLabel(Request $request, Team $team)
    {
        \Gate::authorize('view', $team);

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:64'],
        ]);

        DB::table('team_user')
            ->where('team_id', $team->id)
            ->where('user_id', auth()->id())
            ->update(['label' => $validated['label'] ?? null]);

        return redirect()->route('teams.show', $team)
            ->with('success', 'Label bijgewerkt.');
    }
}
