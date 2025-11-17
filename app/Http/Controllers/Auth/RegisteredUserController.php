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
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'team_name' => ['required', 'string', 'max:255'],
            'maps_location' => ['required', 'string', 'max:2048'],
            'logo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        // Store the logo
        $logoPath = $request->file('logo')->store('logos', 'public');

        DB::transaction(function () use ($request, $logoPath, &$user) {
            // Create user with invite code
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 2, // Default to regular user
                'is_active' => true,
            ]);

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
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
