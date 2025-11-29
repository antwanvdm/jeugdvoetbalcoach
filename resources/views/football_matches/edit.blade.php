<x-app-layout>
    <h1 class="text-2xl font-semibold mb-4">Bewerk wedstrijd</h1>
    <form action="{{ route('football-matches.update', $footballMatch) }}" method="POST" class="bg-white p-4 shadow rounded max-w-3xl">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Tegenstander</label>
            <select name="opponent_id" class="w-full border rounded p-2" required>
                @foreach($opponents as $id => $name)
                    <option value="{{ $id }}" @selected(old('opponent_id', $footballMatch->opponent_id)==$id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Locatie</label>
            <div class="flex items-center gap-6">
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="home" value="1" @checked(old('home', (string)(int)$footballMatch->home)==='1')> Thuis
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="home" value="0" @checked(old('home', (string)(int)$footballMatch->home)==='0')> Uit
                </label>
            </div>
        </div>

        <div class="mb-3 grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">Doelpunten gemaakt</label>
                <input type="number" name="goals_scored" value="{{ old('goals_scored', $footballMatch->goals_scored) }}" class="w-full border rounded p-2" min="0">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Doelpunten tegen</label>
                <input type="number" name="goals_conceded" value="{{ old('goals_conceded', $footballMatch->goals_conceded) }}" class="w-full border rounded p-2" min="0">
            </div>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Datum en tijd</label>
            <input type="datetime-local" name="date" value="{{ old('date', optional($footballMatch->date)->format('Y-m-d\TH:i')) }}" class="w-full border rounded p-2" required>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Seizoen</label>
            <select name="season_id" class="w-full border rounded p-2">
                <option value="">Standaard</option>
                @foreach($seasonsMapped as $id => $label)
                    <option value="{{ $id }}" {{ (old('season_id', $footballMatch->season_id) == $id) ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Notities coach (optioneel)</label>
            <textarea name="notes" rows="3" class="w-full border rounded p-2" placeholder="Gedachten over de wedstrijd...">{{ old('notes', $footballMatch->notes) }}</textarea>
            <p class="text-xs text-gray-600 mt-1">Privé notities; alleen zichtbaar voor coaches.</p>
        </div>

        @if($season && $season->track_goals)
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-3">Doelpunten beheren</h2>
                <div id="goals-container">
                    @foreach($goals as $index => $goal)
                        <div class="goal-row bg-gray-50 p-3 rounded mb-2 grid grid-cols-1 sm:grid-cols-6 gap-2 items-start">
                            <input type="hidden" name="goals[{{ $index }}][id]" value="{{ $goal->id }}">
                            <!-- Rij 1: Speler, Minuut (smaller), Type, Assist -->
                            <div>
                                <label class="text-xs text-gray-600">Speler</label>
                                <select name="goals[{{ $index }}][player_id]" class="w-full border rounded p-1 text-sm">
                                    <option value="">Eigen goal</option>
                                    @foreach($players as $player)
                                        <option value="{{ $player->id }}" {{ $goal->player_id == $player->id ? 'selected' : '' }}>{{ $player->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">Minuut</label>
                                <input type="number" name="goals[{{ $index }}][minute]" value="{{ $goal->minute }}" class="w-20 border rounded p-1 text-sm" min="0" max="120" placeholder="—">
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">Type</label>
                                <input type="text" name="goals[{{ $index }}][subtype]" value="{{ $goal->subtype }}" class="w-full border rounded p-1 text-sm" placeholder="Bijv. penalty">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="text-xs text-gray-600">Assist</label>
                                <select name="goals[{{ $index }}][assist_player_id]" class="w-full border rounded p-1 text-sm">
                                    <option value="">Onbekend</option>
                                    @foreach($players as $player)
                                        <option value="{{ $player->id }}" {{ $goal->assist_player_id == $player->id ? 'selected' : '' }}>{{ $player->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row-span-2 flex items-center sm:justify-center">
                                <button type="button" class="remove-goal-btn mt-6 px-2 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600">Verwijder</button>
                                <input type="hidden" name="goals[{{ $index }}][_delete]" value="0" class="delete-flag">
                            </div>

                            <!-- Rij 2: Notities over volledige breedte (minus delete knop kolom) -->
                            <div class="sm:col-span-5">
                                <label class="text-xs text-gray-600">Notities</label>
                                <textarea name="goals[{{ $index }}][notes]" rows="2" class="w-full border rounded p-2 text-sm" placeholder="—">{{ $goal->notes }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-goal-btn" class="mt-2 px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">+ Doelpunt toevoegen</button>
            </div>
        @endif

        <div class="flex gap-2">
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Opslaan</button>
            <a href="{{ route('football-matches.show', $footballMatch) }}" class="px-3 py-2 bg-gray-200 rounded">Annuleren</a>
        </div>
    </form>

    @if($season && $season->track_goals)
        <template id="goal-row-template">
            <div class="goal-row bg-gray-50 p-3 rounded mb-2 grid grid-cols-1 sm:grid-cols-6 gap-2 items-start">
                <!-- Rij 1 -->
                <div>
                    <label class="text-xs text-gray-600">Speler</label>
                    <select name="goals[__INDEX__][player_id]" class="w-full border rounded p-1 text-sm">
                        <option value="">Eigen goal</option>
                        @foreach($players as $player)
                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-600">Minuut</label>
                    <input type="number" name="goals[__INDEX__][minute]" class="w-20 border rounded p-1 text-sm" min="0" max="120" placeholder="—">
                </div>
                <div>
                    <label class="text-xs text-gray-600">Type</label>
                    <input type="text" name="goals[__INDEX__][subtype]" class="w-full border rounded p-1 text-sm" placeholder="Bijv. penalty">
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs text-gray-600">Assist</label>
                    <select name="goals[__INDEX__][assist_player_id]" class="w-full border rounded p-1 text-sm">
                        <option value="">Onbekend</option>
                        @foreach($players as $player)
                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end sm:justify-center">
                    <button type="button" class="remove-goal-btn mt-6 px-2 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600">Verwijder</button>
                    <input type="hidden" name="goals[__INDEX__][_delete]" value="0" class="delete-flag">
                </div>
                <!-- Rij 2 -->
                <div class="sm:col-span-5">
                    <label class="text-xs text-gray-600">Notities</label>
                    <textarea name="goals[__INDEX__][notes]" rows="2" class="w-full border rounded p-2 text-sm" placeholder="—"></textarea>
                </div>
                <div class="hidden sm:block"></div>
            </div>
        </template>
    @endif
</x-app-layout>
