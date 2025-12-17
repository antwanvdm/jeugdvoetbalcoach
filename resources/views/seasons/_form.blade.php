<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm text-gray-600 dark:text-gray-300">Startjaar seizoen</label>
        <div class="flex items-center gap-2">
            <input type="number" name="year" id="season-year" value="{{ old('year', $season->year ?? '') }}" class="mt-1 block flex-1 border dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 p-2 rounded" min="2000" max="2100">
            <span id="season-year-preview" class="flex-1 text-gray-500 dark:text-gray-400 italic text-sm"></span>
        </div>
        @error('year')
        <div class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</div>@enderror
    </div>
    <div>
        <label class="block text-sm text-gray-600 dark:text-gray-300">Fase <span class="text-xs text-gray-500 dark:text-gray-400">(1 t/m 4)</span></label>
        <input type="number" name="part" id="season-phase" min="1" max="4" value="{{ old('part', $season->part ?? 1) }}" class="mt-1 block w-full border dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 p-2 rounded" oninput="validatePhaseInput(this)">
        <div id="phase-error" class="text-red-600 dark:text-red-400 text-sm" style="display:none;">Voer een getal tussen 1 en 4 in.</div>
        @error('part')
        <div class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="block text-sm text-gray-600 dark:text-gray-300">Startdatum</label>
        <input type="date" name="start" value="{{ old('start', isset($season) ? $season->start->format('Y-m-d') : '') }}" class="mt-1 block w-full border dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 p-2 rounded">
        @error('start')
        <div class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="block text-sm text-gray-600 dark:text-gray-300">Einddatum</label>
        <input type="date" name="end" value="{{ old('end', isset($season) ? $season->end->format('Y-m-d') : '') }}" class="mt-1 block w-full border dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 p-2 rounded">
        @error('end')
        <div class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="block text-sm text-gray-600 dark:text-gray-300">Formatie</label>
        <select name="formation_id" class="mt-1 block w-full border dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 p-2 rounded">
            @foreach($formations as $id => $label)
                <option value="{{ $id }}" {{ (old('formation_id', $season->formation_id ?? '') == $id) ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('formation_id')
        <div class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
            <input type="hidden" name="track_goals" value="0">
            <input type="checkbox" name="track_goals" value="1" {{ old('track_goals', $season->track_goals ?? false) ? 'checked' : '' }} class="rounded">
            <span>Doelpunten bijhouden</span>
        </label>
        <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">Houd doelpunten en assists bij per wedstrijd.</p>
        @error('track_goals')
        <div class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</div>@enderror
    </div>
</div>
