<x-app-layout>
    <div class="max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                @if($team->opponent?->logo)
                    <img src="{{ asset('storage/' . $team->opponent->logo) }}" alt="{{ $team->opponent->name }}" class="h-16 w-16 object-contain">
                @endif
                <div>
                    <h1 class="text-3xl font-bold">{{ $team->opponent?->name }}</h1>
                    @if($team->opponent)
                        <p class="text-gray-600 mt-1">📍 <a class="text-blue-600 hover:underline" target="_blank" href="{{ $team->opponent->location_maps_link }}">Google Maps</a></p>
                    @endif
                </div>
            </div>
            <a href="{{ route('teams.index') }}" class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">Terug</a>
        </div>

        <!-- Team Members -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-4 border-b">
                <h2 class="text-xl font-semibold">Coaches ({{ $members->count() }})</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-3 text-sm font-medium text-gray-700">Naam</th>
                            <th class="text-left p-3 text-sm font-medium text-gray-700">E-mail</th>
                            <th class="text-left p-3 text-sm font-medium text-gray-700">Rol</th>
                            <th class="text-left p-3 text-sm font-medium text-gray-700">Label</th>
                            <th class="text-left p-3 text-sm font-medium text-gray-700">Lid sinds</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($members as $member)
                            <tr class="hover:bg-gray-50">
                                <td class="p-3">
                                    <div class="font-medium">{{ $member['name'] }}</div>
                                </td>
                                <td class="p-3 text-gray-600">{{ $member['email'] }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-1 text-xs rounded {{ $member['role'] === 1 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $member['role_label'] }}
                                    </span>
                                </td>
                                <td class="p-3 text-gray-700">{{ $member['label'] ?? '—' }}</td>
                                <td class="p-3 text-gray-600">{{ $member['joined_at']->format('d-m-Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Invite Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Uitnodiging</h2>
            <p class="text-sm text-gray-600 mb-4">Deel deze link om nieuwe coaches uit te nodigen voor dit team.</p>

            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center gap-2">
                    <input
                        type="text"
                        readonly
                        value="{{ route('teams.join.show', $team->invite_code) }}"
                        class="flex-1 border rounded p-2 bg-white text-sm"
                        id="invite-link-{{ $team->id }}"
                    >
                    <button
                        type="button"
                        data-copy-to-clipboard="{{ route('teams.join.show', $team->invite_code) }}"
                        data-copy-message="Uitnodigingslink gekopieerd!"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 whitespace-nowrap cursor-pointer"
                    >
                        📋 Kopieer link
                    </button>
                </div>
            </div>

            @can('update', $team)
                <div class="mt-4 pt-4 border-t">
                    <form method="POST" action="{{ route('teams.invite.regenerate', $team) }}" onsubmit="return confirm('Weet je zeker dat je een nieuwe uitnodigingscode wilt genereren? De oude link werkt dan niet meer.');">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800 underline cursor-pointer">
                            🔄 Nieuwe uitnodigingscode genereren
                        </button>
                        <p class="text-xs text-gray-500 mt-1">Genereer een nieuwe code als de oude link op straat is beland.</p>
                    </form>
                </div>
            @endcan
        </div>

        <!-- Actions -->
        @can('update', $team)
            <div class="mt-6">
                <a href="{{ route('teams.edit', $team) }}" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                    Team Bewerken
                </a>
            </div>
        @endcan

        <!-- Update own label -->
        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-3">Mijn teamlabel</h2>
            <form method="POST" action="{{ route('teams.label.update', $team) }}" class="max-w-md">
                @csrf
                @method('PATCH')
                <input type="text" name="label" value="{{ old('label', auth()->user()->teams()->where('teams.id', $team->id)->first()?->pivot->label) }}" class="w-full border rounded p-2" placeholder="Bijv. JO8-1">
                <p class="text-xs text-gray-600 mt-1">Alleen zichtbaar voor jou; helpt bij onderscheid tussen teams.</p>
                <div class="mt-3">
                    <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Opslaan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
