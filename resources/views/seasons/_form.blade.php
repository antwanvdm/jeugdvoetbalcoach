<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm text-gray-600">Startjaar seizoen</label>
        <input type="number" name="year" value="{{ old('year', $season->year ?? '') }}" class="mt-1 block w-full border p-2 rounded">
        @error('year')
        <div class="text-red-600 text-sm">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="block text-sm text-gray-600">Deel</label>
        <input type="number" name="part" value="{{ old('part', $season->part ?? 1) }}" class="mt-1 block w-full border p-2 rounded">
        @error('part')
        <div class="text-red-600 text-sm">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="block text-sm text-gray-600">Startdatum</label>
        <input type="date" name="start" value="{{ old('start', isset($season) ? $season->start->format('Y-m-d') : '') }}" class="mt-1 block w-full border p-2 rounded">
        @error('start')
        <div class="text-red-600 text-sm">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="block text-sm text-gray-600">Einddatum</label>
        <input type="date" name="end" value="{{ old('end', isset($season) ? $season->end->format('Y-m-d') : '') }}" class="mt-1 block w-full border p-2 rounded">
        @error('end')
        <div class="text-red-600 text-sm">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="block text-sm text-gray-600">Formatie</label>
        <select name="formation_id" class="mt-1 block w-full border p-2 rounded">
            @foreach($formations as $id => $label)
                <option value="{{ $id }}" {{ (old('formation_id', $season->formation_id ?? '') == $id) ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('formation_id')
        <div class="text-red-600 text-sm">{{ $message }}</div>@enderror
    </div>
</div>
