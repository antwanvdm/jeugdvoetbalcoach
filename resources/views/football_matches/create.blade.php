<x-app-layout>
    <h1 class="text-2xl font-semibold mb-4">Voeg nieuwe wedstrijd toe</h1>
    <form action="{{ route('football-matches.store') }}" method="POST" class="bg-white p-4 shadow rounded max-w-xl" id="matchForm">
        @csrf

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

        <div class="mb-3 grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">Doelpunten gemaakt</label>
                <input type="number" name="goals_scored" value="{{ old('goals_scored') }}" class="w-full border rounded p-2" min="0">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Doelpunten tegen</label>
                <input type="number" name="goals_conceded" value="{{ old('goals_conceded') }}" class="w-full border rounded p-2" min="0">
            </div>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Datum en tijd</label>
            <input type="datetime-local" name="date" value="{{ old('date') }}" class="w-full border rounded p-2" required>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Seizoen</label>
            <select name="season_id" id="season_id" class="w-full border rounded p-2">
                <option value="">-- Kies seizoen --</option>
                @foreach($seasonsMapped as $id => $label)
                    <option value="{{ $id }}" {{ (old('season_id') ?? ($activeSeason?->id ?? '')) == $id ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
                <label class="block text-sm font-medium">Aanwezige spelers</label>
                <div class="flex gap-2">
                    <button type="button" id="selectAll" class="text-xs px-2 py-1 bg-gray-200 rounded hover:bg-gray-300">Alles selecteren</button>
                    <button type="button" id="deselectAll" class="text-xs px-2 py-1 bg-gray-200 rounded hover:bg-gray-300">Alles deselecteren</button>
                </div>
            </div>
            <div id="playersContainer" class="border rounded p-3 bg-gray-50 max-h-64 overflow-y-auto">
                @if($players->isEmpty())
                    <p class="text-sm text-gray-500" id="noPlayersMessage">Selecteer eerst een seizoen om spelers te zien</p>
                @else
                    <div class="grid grid-cols-1 gap-2" id="playersList">
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

        <div class="flex gap-2">
            <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Opslaan</button>
            <a href="{{ route('football-matches.index') }}" class="px-3 py-2 bg-gray-200 rounded">Annuleren</a>
        </div>
    </form>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const seasonSelect = document.getElementById('season_id');
            const playersContainer = document.getElementById('playersContainer');
            const selectAllBtn = document.getElementById('selectAll');
            const deselectAllBtn = document.getElementById('deselectAll');

            // Handle season change to load players via AJAX
            seasonSelect.addEventListener('change', function() {
                const seasonId = this.value;
                
                if (!seasonId) {
                    playersContainer.innerHTML = '<p class="text-sm text-gray-500" id="noPlayersMessage">Selecteer eerst een seizoen om spelers te zien</p>';
                    return;
                }

                // Show loading state
                playersContainer.innerHTML = '<p class="text-sm text-gray-500">Laden...</p>';

                // Fetch players for selected season
                fetch(`{{ route('football-matches.create') }}?season_id=${seasonId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    // Parse the response and extract the players list
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const playersList = doc.getElementById('playersList');
                    const noPlayersMessage = doc.getElementById('noPlayersMessage');
                    
                    if (playersList) {
                        playersContainer.innerHTML = '';
                        playersContainer.appendChild(playersList);
                    } else if (noPlayersMessage) {
                        playersContainer.innerHTML = noPlayersMessage.outerHTML;
                    }
                })
                .catch(error => {
                    console.error('Error loading players:', error);
                    playersContainer.innerHTML = '<p class="text-sm text-red-500">Fout bij laden van spelers</p>';
                });
            });

            // Select all players
            selectAllBtn.addEventListener('click', function() {
                document.querySelectorAll('.player-checkbox').forEach(cb => cb.checked = true);
            });

            // Deselect all players
            deselectAllBtn.addEventListener('click', function() {
                document.querySelectorAll('.player-checkbox').forEach(cb => cb.checked = false);
            });
        });
    </script>
    @endpush
</x-app-layout>
