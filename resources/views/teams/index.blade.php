<x-app-layout>
    @if($onboardingInProgress)
        <div class="mb-4 p-3 rounded-lg bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 flex items-center justify-between">
            <div class="text-sm text-blue-900 dark:text-blue-100">
                <strong>Teamsetup voor huidige team nog niet afgerond</strong> — Voltooi alle stappen om aan de slag te gaan.
            </div>
            <a href="{{ route('dashboard') }}" class="text-xs px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-600 whitespace-nowrap ml-2">Ga verder →</a>
        </div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Mijn Teams</h1>
        <a href="{{ route('teams.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">Nieuw Team</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-800 shadow dark:shadow-gray-700 rounded">
            <thead>
            <tr class="border-b">
                <th class="text-left p-3">Team</th>
                <th class="text-left p-3 hidden sm:table-cell">Rol</th>
                <th class="text-left p-3 hidden sm:table-cell">Leden</th>
                <th class="text-left p-3 hidden sm:table-cell">Uitnodiging</th>
                <th class="text-left p-3">Status</th>
                <th class="text-right p-3">Acties</th>
            </tr>
            </thead>
            <tbody>
            @forelse($teams as $team)
                <tr class="border-b {{ $team['is_default'] ? 'bg-blue-50 dark:bg-blue-900' : '' }}">
                    <td class="p-3">
                        <div class="flex items-center gap-3">
                            @if($team['logo'])
                                <img src="{{ asset('storage/' . $team['logo']) }}" alt="{{ $team['name'] }}" class="h-10 w-10 object-contain">
                            @endif
                            <div>
                                <div class="font-semibold">
                                    {{ $team['name'] }}
                                    @if(!empty($team['label']))
                                        <span class="text-gray-500 dark:text-gray-400">({{ $team['label'] }})</span>
                                    @endif
                                </div>
                                @if($team['is_default'])
                                    <span class="text-xs text-blue-600 dark:text-blue-400">Standaard team</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="p-3 hidden sm:table-cell">{{ $team['role_label'] }}</td>
                    <td class="p-3 hidden sm:table-cell">{{ $team['users_count'] }}</td>
                    <td class="p-3 hidden sm:table-cell">
                        <button
                            type="button"
                            data-copy-to-clipboard="{{ route('teams.join.show', $team['invite_code']) }}"
                            data-copy-message="Uitnodigingslink gekopieerd!"
                            class="text-xs px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded hover:bg-green-200 cursor-pointer"
                            title="Kopieer uitnodigingslink"
                        >
                            📋 Kopieer link
                        </button>
                    </td>
                    <td class="p-3">
                        @if($currentTeamId == $team['id'])
                            <span class="px-2 py-1 text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">Actief</span>
                        @endif
                        @if($currentTeamId != $team['id'])
                            <form method="POST" action="{{ route('teams.switch', $team['id']) }}" class="inline">
                                @csrf
                                <button type="submit" class="px-2 py-1 text-xs bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 hover:bg-yellow-200 dark:hover:bg-yellow-800 rounded cursor-pointer">Switch</button>
                            </form>
                        @endif
                    </td>
                    <td class="p-3 text-right">
                        <a href="{{ route('teams.show', $team['id']) }}" class="text-blue-600 dark:text-blue-400 mr-2">Details</a>

                        @if(!$team['is_default'])
                            <form method="POST" action="{{ route('teams.set-default', $team['id']) }}" class="inline" onsubmit="return confirm('Weet je zeker dat je dit team als jouw standaard team wilt instellen?')">
                                @csrf
                                <button type="submit" class="text-red-600 dark:text-red-400 mr-2 cursor-pointer">Maak standaard</button>
                            </form>
                        @endif

                        <a href="{{ route('teams.edit', $team['id']) }}" class="text-yellow-600 mr-2 hidden sm:inline">Bewerk</a>

                        @if($team['can_leave'])
                            <form method="POST" action="{{ route('teams.leave', $team['id']) }}" class="inline" onsubmit="return confirm('Weet je zeker dat je dit team wilt verlaten?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-yellow-600 mr-2">Verlaat</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-3 text-center text-gray-500 dark:text-gray-400">Je bent nog geen lid van een team.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

</x-app-layout>
