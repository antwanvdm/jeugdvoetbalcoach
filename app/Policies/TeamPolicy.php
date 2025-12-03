<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TeamPolicy
{
    /**
     * Determine whether the user can view any teams.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can view the team.
     */
    public function view(User $user, Team $team): bool
    {
        return $user->is_active && $user->isMemberOf($team);
    }

    /**
     * Determine whether the user can create teams.
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can update the team.
     */
    public function update(User $user, Team $team): bool
    {
        return $user->is_active && $user->isMemberOf($team);
    }

    /**
     * Determine whether the user can delete the team.
     * Only hoofdcoach can delete the team.
     */
    public function delete(User $user, Team $team): bool
    {
        if (!$user->is_active) {
            return false;
        }

        return $user->isHeadCoach($team);
    }

    /**
     * Determine whether the user can leave the team.
     * User must be a member of at least 2 teams to be able to leave one.
     * Team must have at least 2 coaches (so it's not left without a coach).
     */
    public function leave(User $user, Team $team): bool
    {
        if (!$user->is_active || !$user->isMemberOf($team)) {
            return false;
        }

        // User must have at least 2 teams to be able to leave one
        if ($user->teams()->count() < 2) {
            return false;
        }

        // Team must have at least 2 coaches (otherwise it would be left without a coach)
        $coachCount = DB::table('team_user')
            ->where('team_id', $team->id)
            ->count();

        return $coachCount >= 2;
    }

    /**
     * Determine whether the user can set this team as default.
     */
    public function setDefault(User $user, Team $team): bool
    {
        return $user->is_active && $user->isMemberOf($team);
    }
}
