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
        $user->updates_opt_out = $request->has('updates_opt_out');

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
            // Get all teams where user is a member
            $teams = $user->teams()->get();

            foreach ($teams as $team) {
                $otherMembers = $team->users()->where('users.id', '!=', $user->id)->count();

                if ($otherMembers === 0) {
                    // Scenario 1: Only member - delete team and all related data
                    $this->deleteTeamData($team);
                    $team->delete();
                } else {
                    // Scenario 2: Other members present
                    $isHoofdcoach = $team->users()
                        ->wherePivot('user_id', $user->id)
                        ->wherePivot('role', 1)
                        ->exists();

                    if ($isHoofdcoach) {
                        // Promote first assistant to head coach
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

                    // Only remove the pivot relationship
                    $team->users()->detach($user->id);
                }
            }

            // Delete user account
            $user->delete();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Delete all data associated with a team
     * (only when user is the sole member)
     */
    private function deleteTeamData($team): void
    {
        // Get all player IDs for match goals cleanup
        $playerIds = $team->players()->pluck('id');

        // Delete match goals first (foreign key to players)
        if ($playerIds->isNotEmpty()) {
            DB::table('match_goals')
                ->whereIn('player_id', $playerIds)
                ->orWhereIn('assist_player_id', $playerIds)
                ->delete();
        }

        // Delete matches with pivot data
        foreach ($team->footballMatches as $match) {
            $match->players()->detach();
            $match->delete();
        }

        // Delete players with season associations
        foreach ($team->players as $player) {
            $player->seasons()->detach();
            $player->delete();
        }

        // Delete seasons
        $team->seasons()->delete();

        // Delete formations (only non-global ones)
        $team->formations()->where('is_global', false)->delete();
    }
}
