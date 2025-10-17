
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm text-gray-600">Totaal spelers (incl. keeper)</label>
        <input type="number" name="total_players" value="{{ old('total_players', $formation->total_players ?? '') }}" class="mt-1 block w-full border p-2 rounded">
        @error('total_players')
        <div class="text-red-600 text-sm">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="block text-sm text-gray-600">Opstelling (bv. 2-1-2)</label>
        <input type="text" name="lineup_formation" value="{{ old('lineup_formation', $formation->lineup_formation ?? '') }}" class="mt-1 block w-full border p-2 rounded">
        @error('lineup_formation')
        <div class="text-red-600 text-sm">{{ $message }}</div>@enderror
    </div>
</div>
