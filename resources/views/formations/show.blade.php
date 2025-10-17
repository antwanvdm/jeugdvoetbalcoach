
<x-app-layout>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Formatie {{ $formation->lineup_formation }}</h1>
        <div>
            <a href="{{ route('formations.edit', $formation) }}" class="px-3 py-2 bg-yellow-600 text-white rounded mr-2">Bewerk</a>
            <a href="{{ route('formations.index') }}" class="px-3 py-2 bg-gray-200 rounded">Terug</a>
        </div>
    </div>

    <div class="bg-white p-4 shadow rounded max-w-lg">
        <dl>
            <dt class="text-sm text-gray-600">Totaal spelers</dt>
            <dd class="font-medium mb-2">{{ $formation->total_players }}</dd>

            <dt class="text-sm text-gray-600">Opstelling</dt>
            <dd class="font-medium mb-2">{{ $formation->lineup_formation }}</dd>
        </dl>
    </div>
</x-app-layout>
