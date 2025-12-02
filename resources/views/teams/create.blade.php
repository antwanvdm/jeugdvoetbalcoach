<x-app-layout>
    <h1 class="text-2xl font-semibold mb-4">Nieuw Team</h1>
    <form action="{{ route('teams.store') }}" method="POST" class="bg-white p-4 shadow rounded max-w-2xl">
        @csrf
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Koppel Club (autocomplete)</label>
            <input type="text" data-opponent-autocomplete data-target-hidden="opponent_id" class="w-full border rounded p-2" placeholder="Zoek clubnaam..." autocomplete="off">
            <input type="hidden" name="opponent_id" id="opponent_id" value="{{ old('opponent_id') }}">
            <p class="text-xs text-gray-600 mt-1">Selecteer een club uit de landelijke database. Team-naam/logo/locatie komen voortaan van deze club.</p>
            @error('opponent_id')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Eigen label (optioneel)</label>
            <input type="text" name="label" value="{{ old('label') }}" class="w-full border rounded p-2" placeholder="Bijv. JO13 - maandagavond">
            <p class="text-xs text-gray-600 mt-1">Handige eigen naam als je meerdere teams bij dezelfde club hebt.</p>
            @error('label')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Opslaan</button>
            <a href="{{ route('teams.index') }}" class="px-3 py-2 bg-gray-200 rounded">Annuleren</a>
        </div>
    </form>
</x-app-layout>
