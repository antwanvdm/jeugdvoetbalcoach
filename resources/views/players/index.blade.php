<x-app-layout>
    @if($onboardingInProgress)
        <div class="mb-4 p-3 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-between">
            <div class="text-sm text-blue-900">
                <strong>Teamsetup nog niet afgerond</strong> — Voltooi alle stappen om aan de slag te gaan.
            </div>
            <a href="{{ route('dashboard') }}" class="text-xs px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 whitespace-nowrap ml-2">Ga verder →</a>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row gap-4 sm:items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Spelers</h1>
        <div class="flex items-center gap-4">
            <form method="GET" action="{{ route('players.index') }}">
                <select name="season_id" onchange="this.form.submit()" class="border py-2 pl-2 pr-8 rounded">
                    @foreach($seasons as $s)
                        <option value="{{ $s->id }}" {{ (int)($seasonId ?? 0) === $s->id ? 'selected' : '' }}>{{ $s->year }}/{{ $s->year + 1 }} - Fase {{ $s->part }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('players.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">Nieuwe speler(s)</a>
        </div>
    </div>

    <!-- Algemene rotatie informatie -->
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="text-sm text-green-800">
                <strong class="font-semibold">Automatische line-up generatie:</strong> Het algoritme zorgt ervoor dat alle spelers <strong>evenveel speeltijd</strong> krijgen over de 4 kwarten. Bankbeurten worden eerlijk verdeeld en de
                <strong>aangegeven sterkte</strong> van spelers wordt meegenomen om gebalanceerde teams per kwart te maken.
            </div>
        </div>
    </div>

    @php
        $keeperCount = $players->where('position_id', 1)->count();
    @endphp

    @if($keeperCount > 0)
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm text-blue-800">
                    @if($keeperCount === 1)
                        <strong class="font-semibold">Let op:</strong> Je hebt 1 speler met favoriete positie "Keeper". Deze speler wordt <strong>alleen</strong> ingezet op de keeperspositie en komt <strong>nooit</strong> op de bank.
                    @else
                        <strong class="font-semibold">Let op:</strong> Je hebt {{ $keeperCount }} spelers met favoriete positie "Keeper". Deze spelers worden <strong>alleen</strong> ingezet op de keeperspositie, maar krijgen wel normale bankbeurten
                        (net als andere spelers).
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="overflow-x-auto">
    <table class="min-w-full bg-white shadow rounded">
        <thead>
        <tr class="border-b">
            <th class="text-left p-3">Naam</th>
            <th class="text-left p-3">Favoriete positie</th>
            <th class="text-left p-3">Sterkere speler</th>
            <th class="text-left p-3 hidden sm:table-cell">Keer gekeept</th>
            <th class="text-right p-3"></th>
        </tr>
        </thead>
        <tbody>
        @forelse($players as $player)
            <tr class="border-b">
                <td class="p-3">{{ $player->name }}</td>
                <td class="p-3">{{ $player->position->name ?? '-' }}</td>
                <td class="p-3">
                    @if($player->weight == 2)
                        <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Ja</span>
                    @else
                        <span class="inline-block px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">Nee</span>
                    @endif
                </td>
                <td class="p-3 hidden sm:table-cell">{{ $player->keeper_count ?? 0 }}</td>
                <td class="p-3 text-right">
                    <a class="text-blue-600 mr-2" href="{{ route('players.show', $player) }}">Bekijk</a>
                    <a class="text-yellow-600 mr-2 hidden md:inline" href="{{ route('players.edit', $player) }}">Bewerk</a>
                    <form action="{{ route('players.destroy', $player) }}" method="POST" class="hidden md:inline">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600" onclick="return confirm('Deze speler verwijderen?')">Verwijder</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="p-3 text-center text-gray-500">Voeg eerst spelers toe.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    </div>

    <div class="mt-4">{{ $players->links() }}</div>
</x-app-layout>
