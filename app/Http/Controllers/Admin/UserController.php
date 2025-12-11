<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', User::class);

        $users = User::where('role', '=', 2)
            ->orderByDesc('role')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        // Prevent admin from deactivating themselves or demoting the last admin
        $validated = $request->validate([
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Prevent deactivating self
        if (array_key_exists('is_active', $validated)) {
            if ($user->id === $request->user()->id && (int)$validated['is_active'] === 0) {
                return back()->withErrors(['is_active' => 'Je kunt je eigen account niet deactiveren.']);
            }
        }

        $user->fill([
            'is_active' => $validated['is_active'] ?? $user->is_active,
        ])->save();

        return back()->with('status', 'user-updated');
    }
}
