<x-app-layout>
    <h1 class="text-2xl font-semibold mb-4">Nieuwe speler(s)</h1>
    <form action="{{ route('players.store') }}" method="POST" class="bg-white p-6 shadow rounded max-w-4xl">
        @csrf

        <!-- Players List -->
        <div id="players-container" class="mb-6">
            <div class="mb-4">
                <h2 class="text-lg font-semibold mb-3">Spelers</h2>
                <div class="mb-3">
                    <div class="bg-blue-50 border border-blue-  200 text-blue-900 text-sm rounded p-3 flex items-start gap-2">
                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        <div>
                            <strong>Sterkere speler?</strong> Geef aan of een speler fysiek sterker is dan gemiddeld. Dit helpt om de teams per kwart zo eerlijk mogelijk te verdelen. Het algoritme houdt hier rekening mee bij het maken van de opstelling, zodat sterke en minder sterke spelers zo goed mogelijk worden gebalanceerd.
                        </div>
                    </div>
                    <div class="text-sm text-gray-600 mt-2">
                        Voeg één of meerdere spelers toe. Alle spelers worden aan de geselecteerde seizoenen gekoppeld.
                    </div>
                </div>
            </div>

            <!-- Table Header (hidden on mobile) -->
            <div class="hidden sm:grid sm:grid-cols-[2fr_1.5fr_1fr_auto] gap-3 mb-2 text-sm font-medium text-gray-700 px-3">
                <div>Naam</div>
                <div>Positie</div>
                <div>Sterkere speler</div>
            </div>

            <!-- Players rows will be inserted here -->
            <div id="players-rows">
                <!-- Initial row -->
                <div class="player-row mb-3 p-3 border rounded bg-gray-50" data-row-index="0">
                    <div class="grid grid-cols-1 sm:grid-cols-[2fr_1.5fr_1fr_auto] gap-3 items-start">
                        <div>
                            <label class="block text-sm font-medium mb-1 sm:hidden">Naam</label>
                            <input type="text" name="players[0][name]" value="{{ old('players.0.name') }}"
                                   class="w-full border rounded p-2 @error('players.0.name') border-red-500 @enderror"
                                   placeholder="Naam van de speler" required>
                            @error('players.0.name')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 sm:hidden">Positie</label>
                            <select name="players[0][position_id]" class="w-full border rounded p-2 @error('players.0.position_id') border-red-500 @enderror" required>
                                <option value="">Kies een positie</option>
                                @foreach($positions as $id => $name)
                                    <option value="{{ $id }}" @selected(old('players.0.position_id')==$id)>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('players.0.position_id')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-start h-full">
                            <label class="block text-sm font-medium mb-1 sm:hidden">Sterkere speler</label>
                            <input type="hidden" name="players[0][weight]" value="1">
                            <input type="checkbox" name="players[0][weight]" value="2" {{ old('players.0.weight') == '2' ? 'checked' : '' }} class="h-5 w-5">
                            @error('players.0.weight')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="flex items-start sm:items-center sm:justify-center">
                            <button type="button" class="remove-player-btn w-10 h-10 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition hidden" title="Verwijder speler">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Player Button -->
            <button type="button" id="add-player-btn" class="mt-3 px-4 py-2 bg-green-100 text-green-700 hover:bg-green-200 rounded transition inline-flex items-center gap-2 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nog een speler toevoegen
            </button>
        </div>

        <!-- Seasons Section -->
        <div class="mb-6 p-4 border-t pt-6">
            <label class="block text-sm font-medium mb-2">Seizoenen <span class="text-red-500">*</span></label>
            <select name="seasons[]" multiple size="5"
                    class="w-full border rounded p-2 @error('seasons') border-red-500 @enderror @error('seasons.*') border-red-500 @enderror"
                    required>
                @foreach($seasons as $id => $label)
                    <option value="{{ $id }}"
                        {{ in_array($id, old('seasons', [])) ? 'selected' : '' }}
                        {{ !old('seasons') && $currentSeason && $currentSeason->id == $id ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <div class="text-sm text-gray-500 mt-1">Houd Cmd/Ctrl ingedrukt om meerdere seizoenen te selecteren. Minimaal 1 seizoen is verplicht.</div>
            @error('seasons')
            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
            @enderror
            @error('seasons.*')
            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 pt-4 border-t">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                Speler(s) opslaan
            </button>
            <a href="{{ route('players.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded transition">
                Annuleren
            </a>
        </div>
    </form>
</x-app-layout>
