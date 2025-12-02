
<x-app-layout>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Formatie {{ $formation->lineup_formation }}</h1>
        <div>
            @can('update', $formation)
                <a href="{{ route('formations.edit', $formation) }}" class="px-3 py-2 bg-yellow-600 text-white rounded mr-2">Bewerk</a>
            @endcan
            <a href="{{ route('formations.index') }}" class="px-3 py-2 bg-gray-200 rounded">Terug</a>
        </div>
    </div>

    <div class="bg-white p-4 shadow rounded max-w-2xl">
        <dl>
            <dt class="text-sm text-gray-600">Totaal spelers</dt>
            <dd class="font-medium mb-2">{{ $formation->total_players }}</dd>

            <dt class="text-sm text-gray-600">Opstelling</dt>
            <dd class="font-medium mb-2">{{ $formation->lineup_formation }}</dd>

            <dt class="text-sm text-gray-600">Toegevoegd door</dt>
            <dd class="font-medium mb-2">
                @if($formation->is_global)
                    <span class="inline-flex items-center text-xs px-2 py-1 bg-gray-100 rounded">Globaal</span>
                @elseif($formation->user)
                    {{ $formation->user->name }}
                @else
                    —
                @endif
            </dd>
        </dl>
    </div>
</x-app-layout>
