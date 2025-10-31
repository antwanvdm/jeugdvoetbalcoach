<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('role', '=', 2)
            ->orderByDesc('role')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        // Prevent admin from deactivating themselves or demoting the last admin
        $validated = $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'team_name' => ['nullable', 'string', 'max:255'],
        ]);

        // Prevent deactivating self
        if (array_key_exists('is_active', $validated)) {
            if ($user->id === $request->user()->id && (int)$validated['is_active'] === 0) {
                return back()->withErrors(['is_active' => 'Je kunt je eigen account niet deactiveren.']);
            }
        }

        $user->fill([
            'is_active' => $validated['is_active'] ?? $user->is_active,
            'team_name' => $validated['team_name'] ?? $user->team_name,
        ])->save();

        return back()->with('status', 'user-updated');
    }
}
