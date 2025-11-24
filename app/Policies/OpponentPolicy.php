<?php

namespace App\Policies;

use App\Models\Opponent;
use App\Models\User;

class OpponentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Opponent $opponent): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Opponent $opponent): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Opponent $opponent): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Opponent $opponent): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Opponent $opponent): bool
    {
        return $user->isAdmin();
    }
}
