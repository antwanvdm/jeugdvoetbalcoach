<?php

namespace App\Policies;

use App\Models\Season;
use App\Models\User;

class SeasonPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Season $season): bool
    {
        return $user->is_active && $user->isMemberOf($season->team);
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Season $season): bool
    {
        return $user->is_active && $user->isMemberOf($season->team);
    }

    public function delete(User $user, Season $season): bool
    {
        return $user->is_active && $user->isMemberOf($season->team);
    }

    public function restore(User $user, Season $season): bool
    {
        return $user->is_active && $user->isMemberOf($season->team);
    }

    public function forceDelete(User $user, Season $season): bool
    {
        return $user->is_active && $user->isMemberOf($season->team);
    }
}
