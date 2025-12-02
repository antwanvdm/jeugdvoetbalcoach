<x-app-layout>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Speler: {{ $player->name }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('players.index') }}" class="px-3 py-2 bg-gray-200 rounded">Terug</a>
            <a href="{{ route('players.edit', $player) }}" class="px-3 py-2 bg-yellow-500 text-white rounded">Bewerk</a>
            <form action="{{ route('players.destroy', $player) }}" method="POST" onsubmit="return confirm('Deze speler verwijderen?')">
                @csrf
                @method('DELETE')
                <button class="px-3 py-2 bg-red-600 text-white rounded">Verwijder</button>
            </form>
        </div>
    </div>

    <div class="bg-white p-4 shadow rounded max-w-2xl">
        <div class="mb-2">
            <div class="text-sm text-gray-600">Naam</div>
            <div class="font-medium">{{ $player->name }}</div>
        </div>
        <div class="mb-2">
            <div class="text-sm text-gray-600">Favoriete positie</div>
            <div class="font-medium">{{ $player->position->name ?? '-' }}</div>
        </div>
        <div class="mb-2">
            <div class="text-sm text-gray-600">Fysiek</div>
            <div class="font-medium">{{ $player->weight }}</div>
        </div>
    </div>

</x-app-layout>
