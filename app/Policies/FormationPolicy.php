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
        // Users can view global formations or their team's formations
        return $user->is_active && ($formation->is_global || $user->isMemberOf($formation->team));
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
        // Users can update their team's (non-global) formations
        return $user->is_active && !$formation->is_global && $user->isMemberOf($formation->team);
    }

    public function delete(User $user, Formation $formation): bool
    {
        // Admins can delete all formations
        if ($user->isAdmin()) {
            return true;
        }
        // Users can delete their team's (non-global) formations
        return $user->is_active && !$formation->is_global && $user->isMemberOf($formation->team);
    }

    public function restore(User $user, Formation $formation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return $user->is_active && !$formation->is_global && $user->isMemberOf($formation->team);
    }

    public function forceDelete(User $user, Formation $formation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return $user->is_active && !$formation->is_global && $user->isMemberOf($formation->team);
    }
}
