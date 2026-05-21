@push('head')
    <meta name="robots" content="noindex,nofollow">
@endpush
<x-app-layout>
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                @if($seasons->first()->team->opponent?->logo)
                    <img src="{{ asset('storage/' . $seasons->first()->team->opponent->logo) }}" alt="{{ $seasons->first()->team->opponent->name }} logo" class="h-16 w-16 object-contain">
                @endif
                <div>
                    <h1 class="text-3xl font-bold">Jaaroverzicht {{ $year }}/{{ $year + 1 }}</h1>
                    <p class="text-gray-600 dark:text-gray-300">{{ $dateFrom }} – {{ $dateTo }}</p>
                </div>
            </div>
            @if(!$isPublic)
                <a href="{{ route('seasons.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">← Alle seizoenen</a>
            @endif
        </div>

        <!-- Share Section -->
        @auth
            <div class="mt-6 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 p-4 shadow dark:shadow-gray-700 rounded parents-invite mb-6">
                <h2 class="text-lg font-semibold mb-2 text-blue-900 dark:text-blue-100">📱 Deel met ouders</h2>
                <p class="text-sm text-blue-800 dark:text-blue-200 mb-3">Ouders kunnen dit jaaroverzicht bekijken zonder in te loggen via onderstaande link:</p>
                <div class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">
                    <input
                        type="text"
                        readonly
                        value="{{ route('seasons.year.share', ['year' => $year, 'shareToken' => $yearShareToken]) }}"
                        id="yearShareLink"
                        class="flex-1 px-3 py-2 border border-blue-300 rounded bg-white dark:bg-gray-800 text-sm font-mono"
                    >
                    <button
                        data-copy-input="yearShareLink"
                        data-copy-message="Link gekopieerd naar klembord!"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-600 transition whitespace-nowrap cursor-pointer"
                    >
                        Kopieer
                    </button>
                    <button
                        type="button"
                        data-share-input="yearShareLink"
                        data-share-title="Jaaroverzicht {{ $year }}/{{ $year + 1 }}"
                        data-share-text="Bekijk het jaaroverzicht {{ $year }}/{{ $year + 1 }} van {{ $seasons->first()->team->opponent?->name }}"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition whitespace-nowrap cursor-pointer"
                    >
                        Deel
                    </button>
                </div>
                <p class="text-xs text-blue-700 dark:text-blue-300 mt-2">💡 Deze link is uniek en privé – deel alleen met betrokken ouders.</p>
            </div>
        @endauth

        <!-- Fase overzicht -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-700 p-4 mb-6">
            <h2 class="text-lg font-semibold mb-3">Fases in dit jaar</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($seasons->sortBy('part') as $season)
                    @if($isPublic)
                        <a href="{{ route('seasons.share', ['season' => $season, 'shareToken' => $season->share_token]) }}" class="px-3 py-1.5 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded text-sm hover:bg-indigo-200 dark:hover:bg-indigo-800 transition">
                    @else
                        <a href="{{ route('seasons.show', $season) }}" class="px-3 py-1.5 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded text-sm hover:bg-indigo-200 dark:hover:bg-indigo-800 transition">
                    @endif
                        Fase {{ $season->part }}
                        @if($season->start && $season->end)
                            <span class="text-indigo-500 dark:text-indigo-400 ml-1">({{ $season->start->format('d-m-Y') }} – {{ $season->end->format('d-m-Y') }})</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Statistics & Coaches -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
            <!-- Season Statistics (2/3) -->
            <div class="sm:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-700 p-4">
                <h2 class="text-lg font-semibold mb-4">Jaarstatistieken</h2>
                @if($stats['total'] > 0)
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-300">Wedstrijden:</span>
                            <span class="font-semibold">{{ $stats['total'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-300">Prestatie:</span>
                            <span class="font-semibold">
                                <span class="text-green-600 dark:text-green-400">{{ $stats['wins'] }}W</span>
                                <span class="text-gray-400 mx-1">•</span>
                                <span class="text-gray-600 dark:text-gray-300">{{ $stats['draws'] }}G</span>
                                <span class="text-gray-400 mx-1">•</span>
                                <span class="text-red-600 dark:text-red-400">{{ $stats['losses'] }}V</span>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-300">Doelpunten:</span>
                            <span class="font-semibold">{{ $stats['goals_for'] }} voor – {{ $stats['goals_against'] }} tegen</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-300">Doelsaldo:</span>
                            <span class="font-semibold {{ $stats['goal_diff'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $stats['goal_diff'] > 0 ? '+' : '' }}{{ $stats['goal_diff'] }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nog geen wedstrijden met resultaat in dit jaar.</p>
                @endif
            </div>

            <!-- Coaches (1/3) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-700 p-4">
                <h2 class="text-lg font-semibold mb-4">Coaches</h2>
                @if($coaches->count() > 0)
                    <div class="space-y-2">
                        @foreach($coaches as $coach)
                            <div class="flex items-center gap-2 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="text-gray-900 dark:text-gray-100">{{ $coach->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Geen coaches gekoppeld aan dit team.</p>
                @endif
            </div>
        </div>

        <!-- All Matches -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-700 mb-6">
            <div class="p-4 border-b">
                <h2 class="text-xl font-semibold">Alle wedstrijden ({{ $allMatches->count() }})</h2>
            </div>
            <div class="divide-y">
                @forelse($allMatches as $match)
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-800 dark:bg-gray-900 flex sm:items-center justify-between flex-col sm:flex-row gap-4 sm:gap-0">
                        <div class="flex items-center gap-4">
                            @if($match->opponent?->logo)
                                <img src="{{ asset('storage/' . $match->opponent->logo) }}" alt="{{ $match->opponent->name }}" class="h-10 w-10 object-contain">
                            @endif
                            <div>
                                <div class="font-semibold">{{ $match->opponent?->name ?? 'Onbekend' }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ $match->date->format('d-m-Y H:i') }} – {{ $match->home ? 'Thuis' : 'Uit' }}
                                    <span class="ml-2 text-indigo-500 dark:text-indigo-400">Fase {{ $match->season->part }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            @if(!is_null($match->goals_scored) && !is_null($match->goals_conceded))
                                <x-match-score :match="$match" class="text-lg" />
                            @else
                                <span class="text-gray-500 dark:text-gray-400">—</span>
                            @endif
                            @if($isPublic)
                                <a href="{{ route('football-matches.share', ['footballMatch' => $match, 'shareToken' => $match->share_token]) }}" class="px-3 py-1 text-sm bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded hover:bg-green-200">
                                    Bekijk wedstrijd
                                </a>
                            @else
                                <a href="{{ route('football-matches.show', $match) }}" class="px-3 py-1 text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 rounded hover:bg-blue-200">
                                    Details
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-500 dark:text-gray-400">Geen wedstrijden gevonden.</div>
                @endforelse
            </div>
        </div>

        <!-- Goal Statistics -->
        @if($trackGoals && ($topScorers->isNotEmpty() || $topAssisters->isNotEmpty()))
            <div class="grid grid-cols-2 gap-6 mb-6">
                @if($topScorers->isNotEmpty())
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-700">
                        <div class="p-4 border-b">
                            <h2 class="text-xl font-semibold">Topscorers</h2>
                        </div>
                        <div class="p-4">
                            <table class="min-w-full">
                                <thead>
                                <tr class="text-sm text-gray-600 dark:text-gray-300">
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
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-700">
                        <div class="p-4 border-b">
                            <h2 class="text-xl font-semibold">Top assists</h2>
                        </div>
                        <div class="p-4">
                            <table class="min-w-full">
                                <thead>
                                <tr class="text-sm text-gray-600 dark:text-gray-300">
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

        @if(!$isPublic)
            <div class="flex">
                <a href="{{ route('seasons.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600">← Alle seizoenen</a>
            </div>
        @endif
    </div>
</x-app-layout>
