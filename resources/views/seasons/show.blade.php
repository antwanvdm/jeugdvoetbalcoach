<x-app-layout>
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold">Seizoen {{ $season->year }}-{{ $season->part }}</h1>
            <div>
                <a href="{{ route('seasons.edit', $season) }}" class="px-3 py-2 bg-yellow-600 text-white rounded mr-2">Bewerk</a>
                <a href="{{ route('seasons.index') }}" class="px-3 py-2 bg-gray-200 rounded">Terug</a>
            </div>
        </div>

        <div class="bg-white p-4 shadow rounded">
            <dl class="grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm text-gray-600">Jaar</dt>
                    <dd class="font-medium">{{ $season->year }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Deel</dt>
                    <dd class="font-medium">{{ $season->part }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Start</dt>
                    <dd class="font-medium">{{ $season->start->format('Y-m-d') }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Eind</dt>
                    <dd class="font-medium">{{ $season->end->format('Y-m-d') }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-600">Formatie</dt>
                    <dd class="font-medium">{{ $season->formation?->lineup_formation ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="mt-6">
            <h2 class="text-lg font-semibold mb-2">Gerelateerde spelers & wedstrijden</h2>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white p-4 shadow rounded">
                    <h3 class="font-semibold mb-2">Spelers ({{ $season->players->count() }})</h3>
                    <ul>
                        @foreach($season->players as $player)
                            <li>{{ $player->name }}</li>
                        @endforeach
                    </ul>
                </div>

                <div class="bg-white p-4 shadow rounded">
                    <h3 class="font-semibold mb-2">Wedstrijden ({{ $season->footballMatches->count() }})</h3>
                    <ul>
                        @foreach($season->footballMatches as $match)
                            <li>{{ $match->date->format('Y-m-d') }} - {{ $match->opponent?->name }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
