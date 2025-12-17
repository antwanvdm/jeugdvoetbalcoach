<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold mb-2">Resultaat doorvoeren</h1>
        <p class="text-sm text-gray-600 dark:text-gray-300">Seizoen: <span class="font-medium">{{ $footballMatch->season->year }}/{{ $footballMatch->season->year + 1 }} - Fase {{ $footballMatch->season->part }}</span></p>
        <p class="text-sm text-gray-600 dark:text-gray-300">Tegenstander: <span class="font-medium">{{$footballMatch->opponent->name }}</span></p>
    </div>

    <form action="{{ route('football-matches.update', $footballMatch) }}" method="POST" class="bg-white dark:bg-gray-800 p-4 shadow dark:shadow-gray-700 rounded max-w-5xl">
        @csrf
        @method('PUT')

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
                <label class="block text-sm font-medium mb-1">Doelpunten gemaakt ({{ $footballMatch->team->opponent->name }})</label>
                <input type="number" name="goals_scored" value="{{ old('goals_scored', $footballMatch->goals_scored) }}" class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" min="0">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Doelpunten tegen ({{ $footballMatch->opponent->name }})</label>
                <input type="number" name="goals_conceded" value="{{ old('goals_conceded', $footballMatch->goals_conceded) }}" class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" min="0">
            </div>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Datum en tijd</label>
            <input type="datetime-local" name="date" value="{{ old('date', optional($footballMatch->date)->format('Y-m-d\TH:i')) }}" class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 dark:scheme-dark" required>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Notities coach (optioneel)</label>
            <textarea name="notes" rows="3" class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" placeholder="Gedachten over de wedstrijd...">{{ old('notes', $footballMatch->notes) }}</textarea>
            <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">Privé notities; alleen zichtbaar voor coaches.</p>
        </div>

        @if($season && $season->track_goals)
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-3">Doelpunten beheren</h2>
                <div id="goals-container">
                    @foreach($goals as $index => $goal)
                        <div class="goal-row bg-gray-50 dark:bg-gray-900 p-3 rounded mb-2">
                            <input type="hidden" name="goals[{{ $index }}][id]" value="{{ $goal->id }}">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-3">
                                <div>
                                    <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Speler</label>
                                    <select name="goals[{{ $index }}][player_id]" class="w-full border rounded p-1 text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                        <option value="">Eigen goal</option>
                                        @foreach($players as $player)
                                            <option value="{{ $player->id }}" {{ $goal->player_id == $player->id ? 'selected' : '' }}>{{ $player->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Minuut</label>
                                    <input type="number" name="goals[{{ $index }}][minute]" value="{{ $goal->minute }}" class="w-full border rounded p-1 text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" min="0" max="120" placeholder="—">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Type</label>
                                    <input type="text" name="goals[{{ $index }}][subtype]" value="{{ $goal->subtype }}" class="w-full border rounded p-1 text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" placeholder="Bijv. penalty">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Assist</label>
                                    <select name="goals[{{ $index }}][assist_player_id]" class="w-full border rounded p-1 text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                        <option value="">Onbekend</option>
                                        @foreach($players as $player)
                                            <option value="{{ $player->id }}" {{ $goal->assist_player_id == $player->id ? 'selected' : '' }}>{{ $player->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Notities</label>
                                    <textarea name="goals[{{ $index }}][notes]" rows="2" class="w-full border rounded p-2 text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" placeholder="—">{{ $goal->notes }}</textarea>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="button" class="remove-goal-btn px-2 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600">Verwijder</button>
                                <input type="hidden" name="goals[{{ $index }}][_delete]" value="0" class="delete-flag">
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-goal-btn" class="mt-2 px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 cursor-pointer">+ Doelpunt toevoegen</button>
            </div>
        @endif

        <div class="flex gap-2">
            <button class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 cursor-pointer">Opslaan</button>
            <a href="{{ route('football-matches.show', $footballMatch) }}" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 rounded">Annuleren</a>
        </div>
    </form>

    @if($season && $season->track_goals)
        <template id="goal-row-template">
            <div class="goal-row bg-gray-50 dark:bg-gray-900 p-3 rounded mb-2">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-3">
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Speler</label>
                        <select name="goals[__INDEX__][player_id]" class="w-full border rounded p-1 text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                            <option value="">Eigen goal</option>
                            @foreach($players as $player)
                                <option value="{{ $player->id }}">{{ $player->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Minuut</label>
                        <input type="number" name="goals[__INDEX__][minute]" class="w-full border rounded p-1 text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" min="0" max="120" placeholder="—">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Type</label>
                        <input type="text" name="goals[__INDEX__][subtype]" class="w-full border rounded p-1 text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" placeholder="Bijv. penalty">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Assist</label>
                        <select name="goals[__INDEX__][assist_player_id]" class="w-full border rounded p-1 text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                            <option value="">Onbekend</option>
                            @foreach($players as $player)
                                <option value="{{ $player->id }}">{{ $player->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3">
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Notities</label>
                        <textarea name="goals[__INDEX__][notes]" rows="2" class="w-full border rounded p-2 text-sm bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" placeholder="—"></textarea>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="button" class="remove-goal-btn px-2 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600 cursor-pointer">Verwijder</button>
                    <input type="hidden" name="goals[__INDEX__][_delete]" value="0" class="delete-flag">
                </div>
            </div>
        </template>
    @endif
</x-app-layout>
