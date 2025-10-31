<?php

namespace App\Policies;

use App\Models\FootballMatch;
use App\Models\User;

class FootballMatchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, FootballMatch $footballMatch): bool
    {
        return $user->is_active && $footballMatch->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, FootballMatch $footballMatch): bool
    {
        return $user->is_active && $footballMatch->user_id === $user->id;
    }

    public function delete(User $user, FootballMatch $footballMatch): bool
    {
        return $user->is_active && $footballMatch->user_id === $user->id;
    }

    public function restore(User $user, FootballMatch $footballMatch): bool
    {
        return $user->is_active && $footballMatch->user_id === $user->id;
    }

    public function forceDelete(User $user, FootballMatch $footballMatch): bool
    {
        return $user->is_active && $footballMatch->user_id === $user->id;
    }
}
