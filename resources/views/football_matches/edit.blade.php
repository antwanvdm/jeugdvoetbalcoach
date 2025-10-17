<x-app-layout>
    <h1 class="text-2xl font-semibold mb-4">Bewerk wedstrijd</h1>
    <form action="{{ route('football-matches.update', $footballMatch) }}" method="POST" class="bg-white p-4 shadow rounded max-w-xl">
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

        <div class="flex gap-2">
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Opslaan</button>
            <a href="{{ route('football-matches.show', $footballMatch) }}" class="px-3 py-2 bg-gray-200 rounded">Annuleren</a>
        </div>
    </form>
</x-app-layout>
