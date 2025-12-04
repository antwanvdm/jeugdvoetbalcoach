<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                @if($season->team->opponent?->logo)
                    <img src="{{ asset('storage/' . $season->team->opponent->logo) }}" alt="{{ $season->team->opponent->name }}" class="h-16 w-16 object-contain">
                @endif
                <div>
                    <h1 class="text-3xl font-bold">Seizoen {{ $season->year }}/{{ $season->year + 1 }} - Fase {{ $season->part }}</h1>
                    <p class="text-gray-600">{{ $season->team->opponent?->name }} | {{ $season->start->format('d-m-Y') }} - {{ $season->end->format('d-m-Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Matches -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-4 border-b">
                <h2 class="text-xl font-semibold">Wedstrijden ({{ $matches->count() }})</h2>
            </div>
            <div class="divide-y">
                @forelse($matches as $match)
                    <div class="p-4 hover:bg-gray-50 flex items-center justify-between">
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
                            @if($match->share_token)
                                <a href="{{ route('football-matches.share', ['footballMatch' => $match, 'shareToken' => $match->share_token]) }}" target="_blank" class="px-3 py-1 text-sm bg-green-100 text-green-800 rounded hover:bg-green-200">
                                    Bekijk wedstrijd
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-500">Geen wedstrijden in dit seizoen.</div>
                @endforelse
            </div>
        </div>

        <!-- Goal Statistics -->
        @if($season->track_goals && ($topScorers->isNotEmpty() || $topAssisters->isNotEmpty()))
            <div class="grid grid-cols-2 gap-6">
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
    </div>
</x-app-layout>
