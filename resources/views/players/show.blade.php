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

    <!-- Basis informatie -->
    <div class="bg-white dark:bg-gray-800 p-4 shadow dark:shadow-gray-700 rounded mb-4">
        <h2 class="text-lg font-semibold mb-3">Basis informatie</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
            <div class="mb-2">
                <div class="text-sm text-gray-600 dark:text-gray-300">Keeper status</div>
                <div class="font-medium">
                    @php
                        $teamPlayers = $player->team->players;
                        $keeperCount = $teamPlayers->where('position_id', 1)->count();
                        $wantsToKeepCount = $teamPlayers->where('wants_to_keep', true)->where('position_id', '!=', 1)->count();
                    @endphp
                    @if($player->position_id == 1)
                        <span class="inline-block px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded text-xs font-medium">Vast</span>
                    @elseif($player->wants_to_keep || ($keeperCount === 0 && $wantsToKeepCount === 0))
                        <span class="inline-block px-2 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded text-xs font-medium">Roulatie</span>
                    @else
                        <span class="text-gray-400">Nooit</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistieken -->
    @php
        $matchesPlayed = $player->footballMatches->count();
        $goalsScored = $player->footballMatches()
            ->whereHas('goals', function ($query) use ($player) {
                $query->where('player_id', $player->id);
            })
            ->distinct()
            ->count();
        
        $totalGoals = \App\Models\MatchGoal::where('player_id', $player->id)->count();
        $totalAssists = \App\Models\MatchGoal::where('assist_player_id', $player->id)->count();
        
        $keeperMatches = $player->footballMatches()
            ->whereNotNull('football_match_player.position_id')
            ->wherePivot('position_id', 1)
            ->distinct()
            ->count();
        
        $matchesWithResult = $player->footballMatches()
            ->whereNotNull('goals_scored')
            ->whereNotNull('goals_conceded')
            ->distinct()
            ->get();
        
        $wins = 0;
        $draws = 0;
        $losses = 0;
        
        foreach ($matchesWithResult as $match) {
            if ($match->goals_scored > $match->goals_conceded) {
                $wins++;
            } elseif ($match->goals_scored === $match->goals_conceded) {
                $draws++;
            } else {
                $losses++;
            }
        }
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 p-4 shadow dark:shadow-gray-700 rounded">
            <div class="text-sm text-gray-600 dark:text-gray-300">Gespeelde wedstrijden</div>
            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $matchesPlayed }}</div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-4 shadow dark:shadow-gray-700 rounded">
            <div class="text-sm text-gray-600 dark:text-gray-300">Doelpunten</div>
            <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $totalGoals }}</div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-4 shadow dark:shadow-gray-700 rounded">
            <div class="text-sm text-gray-600 dark:text-gray-300">Assists</div>
            <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $totalAssists }}</div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-4 shadow dark:shadow-gray-700 rounded">
            <div class="text-sm text-gray-600 dark:text-gray-300">Keer keeper geweest</div>
            <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $keeperMatches }}</div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-4 shadow dark:shadow-gray-700 rounded">
            <div class="text-sm text-gray-600 dark:text-gray-300">Winst/Gelijk/Verlies</div>
            <div class="text-2xl font-bold">
                <span class="text-green-600 dark:text-green-400">{{ $wins }}</span> /
                <span class="text-gray-600 dark:text-gray-400">{{ $draws }}</span> /
                <span class="text-red-600 dark:text-red-400">{{ $losses }}</span>
            </div>
        </div>
    </div>

</x-app-layout>
