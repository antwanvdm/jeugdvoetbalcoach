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
        return $user->is_active && $opponent->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Opponent $opponent): bool
    {
        return $user->is_active && $opponent->user_id === $user->id;
    }

    public function delete(User $user, Opponent $opponent): bool
    {
        return $user->is_active && $opponent->user_id === $user->id;
    }

    public function restore(User $user, Opponent $opponent): bool
    {
        return $user->is_active && $opponent->user_id === $user->id;
    }

    public function forceDelete(User $user, Opponent $opponent): bool
    {
        return $user->is_active && $opponent->user_id === $user->id;
    }
}
