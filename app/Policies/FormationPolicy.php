<?php

namespace App\Policies;

use App\Models\Formation;
use App\Models\User;

class FormationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Formation $formation): bool
    {
        // Admins can view all formations
        if ($user->isAdmin()) {
            return true;
        }
        // Users can view global formations or their own
        return $user->is_active && ($formation->is_global || $formation->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Formation $formation): bool
    {
        // Admins can update all formations
        if ($user->isAdmin()) {
            return true;
        }
        // Users can update their own (non-global) formations
        return $user->is_active && !$formation->is_global && $formation->user_id === $user->id;
    }

    public function delete(User $user, Formation $formation): bool
    {
        // Admins can delete all formations
        if ($user->isAdmin()) {
            return true;
        }
        // Users can delete their own (non-global) formations
        return $user->is_active && !$formation->is_global && $formation->user_id === $user->id;
    }

    public function restore(User $user, Formation $formation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return $user->is_active && !$formation->is_global && $formation->user_id === $user->id;
    }

    public function forceDelete(User $user, Formation $formation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return $user->is_active && !$formation->is_global && $formation->user_id === $user->id;
    }
}
