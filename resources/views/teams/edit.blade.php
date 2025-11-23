<x-app-layout>
    <h1 class="text-2xl font-semibold mb-4">Team Bewerken</h1>
    <form action="{{ route('teams.update', $team) }}" method="POST" class="bg-white p-4 shadow rounded max-w-lg">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Gekoppelde Club</label>
            <div class="flex items-center gap-3 mb-2">
                @if($team->opponent?->logo)
                    <img src="{{ asset('storage/' . $team->opponent->logo) }}" alt="{{ $team->opponent->name }}" class="h-10 w-10 object-contain">
                @endif
                <div>
                    <div class="font-semibold">{{ $team->opponent?->name ?? '—' }}</div>
                    @if($team->opponent?->location)
                        <div class="text-xs text-gray-600">{{ $team->opponent->location }}</div>
                    @endif
                </div>
            </div>
            <input type="text" data-opponent-autocomplete data-target-hidden="opponent_id" class="w-full border rounded p-2" placeholder="Zoek en kies een andere club..." autocomplete="off">
            <input type="hidden" name="opponent_id" id="opponent_id" value="{{ old('opponent_id', $team->opponent_id) }}">
            @error('opponent_id')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Opslaan</button>
            <a href="{{ route('teams.index') }}" class="px-3 py-2 bg-gray-200 rounded">Annuleren</a>
        </div>
    </form>
</x-app-layout>
