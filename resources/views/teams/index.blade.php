<x-app-layout>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Mijn Teams</h1>
        <a href="{{ route('teams.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">Nieuw Team</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow rounded">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-3">Team</th>
                    <th class="text-left p-3">Rol</th>
                    <th class="text-left p-3">Leden</th>
                    <th class="text-left p-3">Status</th>
                    <th class="text-right p-3">Acties</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teams as $team)
                    <tr class="border-b {{ $team['is_default'] ? 'bg-blue-50' : '' }}">
                        <td class="p-3">
                            <div class="flex items-center gap-3">
                                @if($team['logo'])
                                    <img src="{{ asset('storage/' . $team['logo']) }}" alt="{{ $team['name'] }}" class="h-10 w-10 object-contain">
                                @endif
                                <div>
                                    <div class="font-semibold">{{ $team['name'] }}</div>
                                    @if($team['is_default'])
                                        <span class="text-xs text-blue-600">Standaard team</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="p-3">{{ $team['role_label'] }}</td>
                        <td class="p-3">{{ $team['users_count'] }}</td>
                        <td class="p-3">
                            @if($currentTeamId == $team['id'])
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Actief</span>
                            @endif
                        </td>
                        <td class="p-3 text-right">
                            @if($currentTeamId != $team['id'])
                                <form method="POST" action="{{ route('teams.switch', $team['id']) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-blue-600 mr-2">Activeer</button>
                                </form>
                            @endif
                            
                            @if(!$team['is_default'])
                                <form method="POST" action="{{ route('teams.set-default', $team['id']) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-blue-600 mr-2">Maak standaard</button>
                                </form>
                            @endif
                            
                            <a href="{{ route('teams.edit', $team['id']) }}" class="text-yellow-600 mr-2">Bewerk</a>
                            
                            <form method="POST" action="{{ route('teams.leave', $team['id']) }}" class="inline" onsubmit="return confirm('Weet je zeker dat je dit team wilt verlaten?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-yellow-600 mr-2">Verlaat</button>
                            </form>
                            
                            @if($team['role'] === 1)
                                <form method="POST" action="{{ route('teams.destroy', $team['id']) }}" class="inline" onsubmit="return confirm('Weet je zeker dat je dit team wilt verwijderen? Alle data wordt verwijderd!')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600">Verwijder</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-3 text-center text-gray-500">Je bent nog geen lid van een team.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
