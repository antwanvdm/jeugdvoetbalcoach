<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 top-row-actions">
        <h1 class="text-2xl font-semibold">Speler: {{ $player->name }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('players.index') }}" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 rounded">Terug</a>
            <a href="{{ route('players.edit', $player) }}" class="px-3 py-2 bg-yellow-500 text-white rounded">Bewerk</a>
            <form action="{{ route('players.destroy', $player) }}" method="POST" onsubmit="return confirm('Deze speler verwijderen?')">
                @csrf
                @method('DELETE')
                <button class="px-3 py-2 bg-red-600 text-white rounded">Verwijder</button>
            </form>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 p-4 shadow dark:shadow-gray-700 rounded max-w-2xl">
        <div class="mb-2">
            <div class="text-sm text-gray-600 dark:text-gray-300">Naam</div>
            <div class="font-medium">{{ $player->name }}</div>
        </div>
        <div class="mb-2">
            <div class="text-sm text-gray-600 dark:text-gray-300">Favoriete positie</div>
            <div class="font-medium">{{ $player->position->name ?? '-' }}</div>
        </div>
        <div class="mb-2">
            <div class="text-sm text-gray-600 dark:text-gray-300">Sterkere speler</div>
            <div class="font-medium">
                @if($player->weight == 2)
                    <span class="inline-block px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs font-medium">Ja</span>
                @else
                    <span class="inline-block px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded text-xs">Nee</span>
                @endif
            </div>
        </div>
    </div>

</x-app-layout>
