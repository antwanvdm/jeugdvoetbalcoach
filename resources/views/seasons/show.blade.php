@push('head')
    <meta name="robots" content="noindex,nofollow">
@endpush
<x-app-layout>
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                @if($season->team->opponent?->logo)
                    <img src="{{ asset('storage/' . $season->team->opponent->logo) }}" alt="{{ $season->team->opponent->name }} logo" class="h-16 w-16 object-contain">
                @endif
                <div>
                    <h1 class="text-3xl font-bold">Seizoen {{ $season->year }}/{{ $season->year + 1 }} - Fase {{ $season->part }}</h1>
                    <p class="text-gray-600">{{ $season->start->format('d-m-Y') }} - {{ $season->end->format('d-m-Y') }}</p>
                </div>
            </div>
            @auth
                <a href="{{ route('seasons.index') }}" class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">Terug</a>
            @endauth
        </div>

        <!-- Share Section (uniform with match share) -->
        @auth
            @if($season->share_token)
                <div class="mt-6 bg-blue-50 border border-blue-200 p-4 shadow rounded parents-invite mb-6">
                    <h2 class="text-lg font-semibold mb-2 text-blue-900">📱 Deel met ouders</h2>
                    <p class="text-sm text-blue-800 mb-3">Ouders kunnen dit seizoen bekijken zonder in te loggen via onderstaande link:</p>
                    <div class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">
                        <input
                            type="text"
                            readonly
                            value="{{ route('seasons.share', ['season' => $season, 'shareToken' => $season->share_token]) }}"
                            id="seasonShareLink"
                            class="flex-1 px-3 py-2 border border-blue-300 rounded bg-white text-sm font-mono"
                        >
                        <button
                            data-copy-input="seasonShareLink"
                            data-copy-message="Link gekopieerd naar klembord!"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition whitespace-nowrap cursor-pointer"
                        >
                            Kopieer
                        </button>
                        <button
                            type="button"
                            data-share-input="seasonShareLink"
                            data-share-title="Seizoen delen"
                            data-share-text="Bekijk {{ $season->team->opponent->name }} Seizoen {{ $season->year }}/{{ $season->year + 1 }} - Fase {{ $season->part }}"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition whitespace-nowrap cursor-pointer"
                        >
                            Deel
                        </button>
                    </div>
                    <p class="text-xs text-blue-700 mt-2">💡 Deze link is uniek en privé - deel alleen met betrokken ouders.</p>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-yellow-800">Nog geen deellink voor dit seizoen. <a href="{{ route('seasons.edit', $season) }}" class="underline font-medium">Genereer een deellink</a> om met ouders te delen.</p>
                </div>
            @endif
        @endauth

        <!-- Statistics & Coaches -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
            <!-- Season Statistics (2/3) -->
            <div class="sm:col-span-2 bg-white rounded-lg shadow p-4">
                <h2 class="text-lg font-semibold mb-4">Seizoensstatistieken</h2>
                @if($season->stats && $season->stats['total'] > 0)
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Wedstrijden:</span>
                            <span class="font-semibold">{{ $season->stats['total'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Prestatie:</span>
                            <span class="font-semibold">
                                <span class="text-green-600">{{ $season->stats['wins'] }}W</span>
                                <span class="text-gray-400 mx-1">•</span>
                                <span class="text-gray-600">{{ $season->stats['draws'] }}G</span>
                                <span class="text-gray-400 mx-1">•</span>
                                <span class="text-red-600">{{ $season->stats['losses'] }}V</span>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Doelsaldo:</span>
                            <span class="font-semibold {{ $season->stats['goal_diff'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $season->stats['goal_diff'] > 0 ? '+' : '' }}{{ $season->stats['goal_diff'] }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Nog geen wedstrijden met resultaat.</p>
                @endif
            </div>

            <!-- Coaches (1/3) -->
            <div class="bg-white rounded-lg shadow p-4">
                <h2 class="text-lg font-semibold mb-4">Coaches</h2>
                @if($coaches->count() > 0)
                    <div class="space-y-2">
                        @foreach($coaches as $coach)
                            <div class="flex items-center gap-2 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="text-gray-900">{{ $coach->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">Geen coaches gekoppeld aan dit team.</p>
                @endif
            </div>
        </div>

        <!-- Matches -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-4 border-b">
                <h2 class="text-xl font-semibold">Wedstrijden ({{ $matches->count() }})</h2>
            </div>
            <div class="divide-y">
                @forelse($matches as $match)
                    <div class="p-4 hover:bg-gray-50 flex sm:items-center justify-between flex-col sm:flex-row gap-4 sm:gap-0">
                        <div class="flex items-center gap-4">
                            @if($match->opponent?->logo)
                                <img src="{{ asset('storage/' . $match->opponent->logo) }}" alt="{{ $match->opponent->name }}" class="h-10 w-10 object-contain">
                            @endif
                            <div>
                                <div class="font-semibold">{{ $match->opponent?->name ?? 'Onbekend' }}</div>
                                <div class="text-sm text-gray-600">{{ $match->date->format('d-m-Y H:i') }} - {{ $match->home ? 'Thuis' : 'Uit' }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            @if(!is_null($match->goals_scored) && !is_null($match->goals_conceded))
                                <span class="font-bold text-lg result-{{$match->result}}">
                                    @if($match->result !== 'O')
                                        @if($match->home)
                                            {{ $match->goals_scored }} - {{ $match->goals_conceded }}
                                        @else
                                            {{ $match->goals_conceded }} - {{ $match->goals_scored }}
                                        @endif
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </span>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                            @auth
                                <a href="{{ route('football-matches.show', $match) }}" class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded hover:bg-blue-200">
                                    Details
                                </a>
                            @else
                                <a href="{{ route('football-matches.share', ['footballMatch' => $match, 'shareToken' => $match->share_token]) }}" target="_blank" class="px-3 py-1 text-sm bg-green-100 text-green-800 rounded hover:bg-green-200">
                                    Bekijk wedstrijd
                                </a>
                            @endauth
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-500">Geen wedstrijden in dit seizoen.</div>
                @endforelse
            </div>
        </div>

        <!-- Goal Statistics -->
        @if($season->track_goals && ($topScorers->isNotEmpty() || $topAssisters->isNotEmpty()))
            <div class="grid grid-cols-2 gap-6 mb-6">
                @if($topScorers->isNotEmpty())
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b">
                            <h2 class="text-xl font-semibold">Topscorers</h2>
                        </div>
                        <div class="p-4">
                            <table class="min-w-full">
                                <thead>
                                <tr class="text-sm text-gray-600">
                                    <th class="text-left pb-2">Speler</th>
                                    <th class="text-right pb-2">Doelpunten</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y">
                                @foreach($topScorers as $scorer)
                                    <tr>
                                        <td class="py-2">{{ $scorer->name }}</td>
                                        <td class="py-2 text-right font-bold">{{ $scorer->goals_count }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if($topAssisters->isNotEmpty())
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-4 border-b">
                            <h2 class="text-xl font-semibold">Top assists</h2>
                        </div>
                        <div class="p-4">
                            <table class="min-w-full">
                                <thead>
                                <tr class="text-sm text-gray-600">
                                    <th class="text-left pb-2">Speler</th>
                                    <th class="text-right pb-2">Assists</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y">
                                @foreach($topAssisters as $assister)
                                    <tr>
                                        <td class="py-2">{{ $assister->name }}</td>
                                        <td class="py-2 text-right font-bold">{{ $assister->assists_count }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @auth
            <!-- Actions -->
            <div class="flex gap-2">
                <a href="{{ route('seasons.edit', $season) }}" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                    Seizoen bewerken
                </a>
            </div>
        @endauth
    </div>
</x-app-layout>
