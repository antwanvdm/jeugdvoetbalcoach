<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Check if user has a pending team invite
        $hasPendingInvite = session()->has('pending_team_invite');
        
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
        
        // Only require team fields if not joining via invite
        if (!$hasPendingInvite) {
            $rules['team_name'] = ['required', 'string', 'max:255'];
            $rules['maps_location'] = ['required', 'string', 'max:2048'];
            $rules['logo'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'];
        }
        
        $request->validate($rules);

        // Check if registering via invite
        $hasPendingInvite = session()->has('pending_team_invite');
        $logoPath = null;
        
        // Only process logo if provided
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        DB::transaction(function () use ($request, $logoPath, $hasPendingInvite, &$user) {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 2, // Default to regular user
                'is_active' => true,
            ]);

            // Only create own team if not joining via invite
            if (!$hasPendingInvite) {
                // Create team
                $team = Team::create([
                    'name' => $request->team_name,
                    'maps_location' => $request->maps_location,
                    'logo' => $logoPath,
                    'invite_code' => Str::random(64),
                ]);

                // Attach user to team as hoofdcoach with default flag
                $user->teams()->attach($team->id, [
                    'role' => 1, // hoofdcoach
                    'is_default' => true,
                    'joined_at' => now(),
                ]);

                // Set team in session
                session(['current_team_id' => $team->id]);
            }
        });

        event(new Registered($user));

        Auth::login($user);

        // Check if user has a pending team invite
        if (session()->has('pending_team_invite')) {
            $inviteCode = session()->pull('pending_team_invite');
            $inviteTeam = Team::where('invite_code', $inviteCode)->first();
            
            if ($inviteTeam && !$user->isMemberOf($inviteTeam)) {
                // Add user as assistent to the invited team
                // Set as default if this is their first/only team
                $isFirstTeam = $user->teams()->count() === 0;
                
                $user->teams()->attach($inviteTeam->id, [
                    'role' => 2, // assistent
                    'is_default' => $isFirstTeam,
                    'joined_at' => now(),
                ]);
                
                // Switch to the invited team
                session(['current_team_id' => $inviteTeam->id]);
                
                return redirect(route('dashboard', absolute: false))
                    ->with('success', "Welkom! Je bent toegevoegd aan {$inviteTeam->name}.");
            }
        }

        return redirect(route('dashboard', absolute: false));
    }
}
