<x-app-layout>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Gebruikers</h1>
    </div>

    @if (session('status') === 'user-updated')
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900 text-green-700 dark:text-green-400 rounded">Gebruiker bijgewerkt.</div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-800 shadow dark:shadow-gray-700 rounded">
            <thead>
            <tr class="border-b">
                <th class="text-left p-3">Naam</th>
                <th class="text-left p-3">Email</th>
                <th class="text-left p-3">Default Team</th>
                <th class="text-left p-3">Actief</th>
                <th class="text-right p-3">Acties</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($users as $user)
                <tr class="border-b">
                    <td class="p-3">{{ $user->name }}</td>
                    <td class="p-3">{{ $user->email }}</td>
                    <td class="p-3">{{ $user->defaultTeam()->opponent->name ?? '—' }}</td>
                    <td class="p-3">
                        @if($user->is_active)
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">Actief</span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">Inactief</span>
                        @endif
                    </td>
                    <td class="p-3 text-right">
                        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="inline-flex items-center gap-2">
                            @csrf
                            @method('PATCH')

                            <select name="is_active" class="border dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 py-1 pl-2 pr-6 rounded text-sm">
                                <option value="1" {{ $user->is_active ? 'selected' : '' }}>Actief</option>
                                <option value="0" {{ ! $user->is_active ? 'selected' : '' }}>Inactief</option>
                            </select>

                            <x-primary-button class="text-xs">Opslaan</x-primary-button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-3 text-center text-gray-500 dark:text-gray-400">Nog geen gebruikers.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</x-app-layout>
