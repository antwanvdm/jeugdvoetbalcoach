<x-app-layout>
    <h1 class="text-2xl font-semibold mb-4">Speler bewerken</h1>
    <form action="{{ route('players.update', $player) }}" method="POST" class="bg-white dark:bg-gray-800 p-4 shadow dark:shadow-gray-700 rounded max-w-4xl">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Naam</label>
            <input type="text" name="name" value="{{ old('name', $player->name) }}" class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" required>
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Positie</label>
            <select name="position_id" id="position_id" class="position-select w-full border rounded p-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" required>
                @foreach($positions as $id => $name)
                    <option value="{{ $id }}" @selected(old('position_id', $player->position_id)==$id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <span class="block text-sm font-medium mb-1">Sterkere speler</span>
            <input type="hidden" name="weight" value="1">
            <input type="checkbox" name="weight" value="2" id="weight" {{ old('weight', $player->weight) == '2' ? 'checked' : '' }} class="align-middle mr-2">
            <label for="weight" class="text-sm cursor-pointer">Sterkere speler</label>
        </div>
        <div class="mb-3 wants-to-keep-container" id="wants-to-keep-container">
            <span class="block text-sm font-medium mb-1">Wil keepen</span>
            <input type="hidden" name="wants_to_keep" value="0">
            <input type="checkbox" name="wants_to_keep" value="1" id="wants_to_keep" {{ old('wants_to_keep', $player->wants_to_keep) ? 'checked' : '' }} class="wants-to-keep-checkbox align-middle mr-2 disabled:opacity-50 disabled:cursor-not-allowed" @if(old('position_id', $player->position_id) == 1) disabled @endif>
            <label for="wants_to_keep" class="text-sm cursor-pointer">Wil keepen (rouleert mee als keeper)</label>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Als deze speler graag wil keepen maar geen vaste keeper is, wordt deze meegenomen in de keeper-roulatie. Niet beschikbaar voor vaste keepers.
            </p>
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Seizoenen</label>
            <select name="seasons[]" multiple class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                @foreach($seasons as $id => $label)
                    <option value="{{ $id }}" {{ in_array($id, old('seasons', $player->seasons->pluck('id')->toArray() )) ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Houd Cmd/Ctrl ingedrukt om meerdere seizoenen te selecteren.</div>
        </div>

        <div class="flex gap-2">
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Werk bij</button>
            <a href="{{ route('players.show', $player) }}" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 rounded">Annuleer</a>
        </div>
    </form>
</x-app-layout>
