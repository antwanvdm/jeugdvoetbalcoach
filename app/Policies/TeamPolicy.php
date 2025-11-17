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

        return $user->isHoofdcoachOf($team);
    }

    /**
     * Determine whether the user can leave the team.
     * Hoofdcoach can only leave if there's at least one assistent.
     */
    public function leave(User $user, Team $team): bool
    {
        if (!$user->is_active || !$user->isMemberOf($team)) {
            return false;
        }

        // If user is hoofdcoach, check if there's at least one assistent
        if ($user->isHoofdcoachOf($team)) {
            $assistentenCount = DB::table('team_user')
                ->where('team_id', $team->id)
                ->where('role', 2)
                ->count();

            return $assistentenCount > 0;
        }

        // Assistenten can always leave
        return true;
    }

    /**
     * Determine whether the user can set this team as default.
     */
    public function setDefault(User $user, Team $team): bool
    {
        return $user->is_active && $user->isMemberOf($team);
    }
}
