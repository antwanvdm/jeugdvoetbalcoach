<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold mb-2">Plan volgende wedstrijd</h1>
        <p class="text-sm text-gray-600">Seizoen: <span class="font-medium">{{ $season->year }}/{{ $season->year + 1 }} - Fase {{ $season->part }}</span></p>
        <p class="text-sm text-blue-900 bg-blue-50 border border-blue-200 rounded p-3 mt-2 mb-4">Je vult hier de basisgegevens in voor de volgende wedstrijd. Op basis hiervan wordt automatisch een gebalanceerde opstelling gegenereerd. Doelpunten kun
            je na afloop invullen.</p>
    </div>

    <form action="{{ route('football-matches.store') }}" method="POST" class="bg-white p-4 shadow rounded max-w-5xl">
        @csrf
        <input type="hidden" name="season_id" value="{{ $season->id }}">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left column: Match details -->
            <div>
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Tegenstander (autocomplete)</label>
                    <input type="text" id="opponent_search" data-opponent-autocomplete data-target-hidden="opponent_id" class="w-full border rounded p-2" placeholder="Typ clubnaam..." autocomplete="off">
                    <input type="hidden" name="opponent_id" id="opponent_id" value="{{ old('opponent_id') }}">
                    <p class="mt-1 text-xs text-gray-500">Begin te typen om een club uit de landelijke database te selecteren.</p>
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Locatie</label>
                    <div class="flex items-center gap-6">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="home" value="1" @checked(old('home','1')==='1')> Thuis
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="home" value="0" @checked(old('home')==='0')> Uit
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Datum en tijd</label>
                    <input type="datetime-local" name="date" value="{{ old('date') }}" class="w-full border rounded p-2" required>
                </div>
            </div>

            <!-- Right column: Available players -->
            <div>
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium">Aanwezige spelers</label>
                        <span class="text-xs text-gray-600">(Minimaal {{ $season->formation->total_players }} spelers)</span>
                    </div>
                    <div class="border rounded p-3 bg-gray-50 overflow-y-auto max-h-96">
                        @if($players->isEmpty())
                            <p class="text-sm text-gray-500">Geen spelers beschikbaar voor dit seizoen</p>
                        @else
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($players as $player)
                                    <label class="inline-flex items-center gap-2 hover:bg-gray-100 p-1 rounded cursor-pointer">
                                        <input
                                            type="checkbox"
                                            name="available_players[]"
                                            value="{{ $player->id }}"
                                            class="player-checkbox"
                                            {{ in_array($player->id, old('available_players', [])) || !old('available_players') ? 'checked' : '' }}
                                        >
                                        <span class="text-sm">{{ $player->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Vink de spelers aan die aanwezig zijn. Standaard zijn alle spelers geselecteerd.</p>
                </div>
            </div>
        </div>

        <div class="flex gap-2 mt-4 pt-4 border-t">
            <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Opslaan</button>
            <a href="{{ route('football-matches.index') }}" class="px-3 py-2 bg-gray-200 rounded">Annuleren</a>
        </div>
    </form>
</x-app-layout>
