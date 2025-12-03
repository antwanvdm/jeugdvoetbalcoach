<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $teams = $request->user()->teams()->with('opponent')->withCount('users')->get();
        
        return view('profile.edit', [
            'user' => $request->user(),
            'teams' => $teams,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Profiel succesvol bijgewerkt.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user) {
            // Haal alle teams op waar user lid van is
            $teams = $user->teams()->get();

            foreach ($teams as $team) {
                $otherMembers = $team->users()->where('users.id', '!=', $user->id)->count();

                if ($otherMembers === 0) {
                    // Scenario 1: Enige lid - verwijder team en alle gerelateerde data
                    $this->deleteTeamData($team);
                    $team->delete();
                } else {
                    // Scenario 2: Andere leden aanwezig
                    $isHoofdcoach = $team->users()
                        ->wherePivot('user_id', $user->id)
                        ->wherePivot('role', 1)
                        ->exists();

                    if ($isHoofdcoach) {
                        // Promoveer eerste assistent tot hoofdcoach
                        $firstAssistent = $team->users()
                            ->wherePivot('role', 2)
                            ->first();

                        if ($firstAssistent) {
                            DB::table('team_user')
                                ->where('team_id', $team->id)
                                ->where('user_id', $firstAssistent->id)
                                ->update(['role' => 1]);
                        }
                    }

                    // Verwijder alleen de pivot relatie
                    $team->users()->detach($user->id);
                }
            }

            // Verwijder user account
            $user->delete();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Verwijder alle data gekoppeld aan een team
     * (alleen wanneer gebruiker enige lid is)
     */
    private function deleteTeamData($team): void
    {
        // Haal alle speler IDs op voor match goals cleanup
        $playerIds = $team->players()->pluck('id');

        // Verwijder match goals eerst (foreign key naar players)
        if ($playerIds->isNotEmpty()) {
            DB::table('match_goals')
                ->whereIn('player_id', $playerIds)
                ->orWhereIn('assist_player_id', $playerIds)
                ->delete();
        }

        // Verwijder wedstrijden met pivot data
        foreach ($team->footballMatches as $match) {
            $match->players()->detach();
            $match->delete();
        }

        // Verwijder spelers met seizoen-koppelingen
        foreach ($team->players as $player) {
            $player->seasons()->detach();
            $player->delete();
        }

        // Verwijder seizoenen
        $team->seasons()->delete();

        // Verwijder formaties (alleen niet-globale)
        $team->formations()->where('is_global', false)->delete();
    }
}
