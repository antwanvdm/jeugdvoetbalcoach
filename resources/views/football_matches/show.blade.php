<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 top-row-actions">
        <h1 class="text-xl sm:text-2xl font-semibold">Wedstrijd tegen {{ $footballMatch->opponent->name ?? 'Onbekend' }}</h1>
        <div class="flex flex-wrap gap-2">
            @auth
                <a href="{{ route('football-matches.index') }}" class="px-3 py-2 bg-gray-200 rounded text-sm">Terug</a>
                <a href="{{ route('football-matches.lineup', $footballMatch) }}" class="px-3 py-2 bg-indigo-600 text-white rounded text-sm">Line-up</a>
                <a href="{{ route('football-matches.edit', $footballMatch) }}" class="px-3 py-2 bg-yellow-500 text-white rounded text-sm">Bewerk</a>
                <form action="{{ route('football-matches.destroy', $footballMatch) }}" method="POST" onsubmit="return confirm('Wedstrijd verwijderen?')">
                    @csrf
                    @method('DELETE')
                    <button class="px-3 py-2 bg-red-600 text-white rounded text-sm">Verwijder</button>
                </form>
            @endauth
        </div>
    </div>

    <div class="bg-white p-4 shadow rounded flex flex-col-reverse sm:flex-row gap-4 opponent-info">
        <dl class="grid grid-cols-2 sm:grid-cols-3 gap-2 flex-1">
            <dt class="text-gray-600 font-bold">Tegenstander</dt>
            <dd class="sm:col-span-2">{{ $footballMatch->opponent->name ?? '-' }}</dd>

            <dt class="text-gray-600 font-bold">Locatie</dt>
            <dd class="sm:col-span-2">
                {{ $footballMatch->home ? 'Thuis' : 'Uit' }} (<a href="{{ $footballMatch->home ? $footballMatch->team->opponent->location_maps_link : $footballMatch->opponent->location_maps_link }}" target="_blank" rel="noopener"
                   class="text-blue-600 hover:underline">{{ $locLabel ?? '📍 Kaart' }}</a>)
            </dd>

            <dt class="text-gray-600 font-bold">Datum</dt>
            <dd class="sm:col-span-2">{{ $footballMatch->date?->translatedFormat('j F Y H:i') }}</dd>

            <dt class="text-gray-600 font-bold">Uitslag</dt>
            <dd class="sm:col-span-2 font-bold result-{{$footballMatch->result}}">
                @if($footballMatch->result !== 'O')
                    {{ $footballMatch->goals_scored }} - {{ $footballMatch->goals_conceded }}
                @else
                    <span class="text-gray-500">-</span>
                @endif
            </dd>
        </dl>
        <div class="flex-1 flex justify-center items-center gap-4 @if(!$footballMatch->home) flex-row-reverse @endif">
            <div class="flex-12 flex @if($footballMatch->home) justify-end @endif">
                <img src="{{ asset('storage/' . $footballMatch->team->opponent->logo) }}" alt="{{ $footballMatch->team->name }} Logo" class="h-28">
            </div>
            <div class="flex-1 text-center text-xl sm:text-2xl">
                -
            </div>
            <div class="flex-12 flex @if(!$footballMatch->home) justify-end @endif">
                <img src="{{asset('storage/' . $footballMatch->opponent->logo)}}" alt="{{$footballMatch->opponent->name}} Logo" class="h-28">
            </div>
        </div>
    </div>

    @auth
        @if($footballMatch->share_token)
            {{-- Share link for parents --}}
            <div class="mt-6 bg-blue-50 border border-blue-200 p-4 shadow rounded parents-invite">
                <h2 class="text-lg font-semibold mb-2 text-blue-900">📱 Deel met ouders</h2>
                <p class="text-sm text-blue-800 mb-3">Ouders kunnen deze wedstrijd bekijken zonder in te loggen via onderstaande link:</p>
                <div class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">
                    <input
                        type="text"
                        readonly
                        value="{{ route('football-matches.share', ['footballMatch' => $footballMatch, 'shareToken' => $footballMatch->share_token]) }}"
                        id="shareLink"
                        class="flex-1 px-3 py-2 border border-blue-300 rounded bg-white text-sm font-mono"
                    >
                    <button
                        data-copy-input="shareLink"
                        data-copy-message="Link gekopieerd naar klembord!"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition whitespace-nowrap"
                    >
                        Kopieer
                    </button>
                </div>
                <p class="text-xs text-blue-700 mt-2">💡 Deze link is uniek en privé - deel alleen met betrokken ouders.</p>
            </div>
        @endif
    @endauth

    {{-- Lineup overview table --}}
    <div class="mt-6 bg-white p-4 shadow rounded">
        <div class="flex flex-col sm:flex-row sm:justify-between gap-2 sm:items-center line-up-header">
            <h2 class="text-xl font-semibold mb-3">Line-up per kwart</h2>
            @auth
                <a href="{{ route('football-matches.lineup', $footballMatch) }}" class="px-3 py-2 bg-indigo-600 text-white rounded text-sm text-center">Bewerk line-up</a>
            @endauth
        </div>
        <div class="overflow-x-auto">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                    <tr class="text-left text-gray-600">
                        <th class="py-2 pr-4 w-20"></th>
                        <th class="py-2 pr-4">Keeper</th>
                        <th class="py-2 pr-4">Spelers</th>
                        <th class="py-2 pr-4">Bank</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach(range(1,4) as $q)
                        @php
                            $present = $assignmentsByQuarter[$q] ?? collect();
                            $bench = $present->filter(fn($p) => is_null($p->position_id));
                            $starters = $present->filter(fn($p) => !is_null($p->position_id));
                            $keeper = $starters->first(function($p) use ($positionNames){
                                $pid = $p->position_id;
                                $n = strtolower($positionNames[$pid] ?? '');
                                return str_contains($n, 'keep') || str_contains($n, 'goal') || str_contains($n, 'doel');
                            });
                            $field = $starters->filter(fn($p) => $keeper ? $p->id !== $keeper->id : true);
                        @endphp
                        <tr class="border-t align-middle">
                            <td class="py-2 pr-4 font-medium">Q{{ $q }}</td>
                            <td class="py-2 pr-4">
                                @if($keeper)
                                    <span class="inline-block px-2 py-1 rounded text-white bg-green-800">{{ $keeper->name }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4">
                                @forelse($field as $p)
                                    <span class="inline-block px-2 py-1 mb-1 sm:mb-0 rounded text-white bg-green-600">{{ $p->name }}</span>
                                @empty
                                    <span class="text-gray-400">-</span>
                                @endforelse
                            </td>
                            <td class="py-2 pr-4">
                                @forelse($bench as $p)
                                    <span class="inline-block px-2 py-1 mb-1 sm:mb-0 rounded bg-yellow-300 text-yellow-900">{{ $p->name }}</span>
                                @empty
                                    <span class="text-gray-400">-</span>
                                @endforelse
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Dobbelsteen-opstelling (schematic) per quarter --}}
    <div class="mt-6 bg-white p-4 shadow rounded">
        <div class="flex flex-col sm:flex-row sm:justify-between gap-2 sm:items-center line-up-header mb-3">
            <h2 class="text-xl font-semibold">Opstelling per kwart</h2>
            <button onclick="window.print();" class="px-3 py-2 bg-indigo-600 text-white rounded cursor-pointer text-sm">🖨️ Print</button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 line-up">
            @foreach(range(1,4) as $q)
                @php
                    $present = $assignmentsByQuarter[$q] ?? collect();
                    $starters = $present->filter(fn($p) => !is_null($p->position_id));
                    $attackers = collect();
                    $midfielders = collect();
                    $defenders = collect();
                    $keepers = collect();
                    foreach ($starters as $p) {
                        $pid = $p->position_id;
                        $n = strtolower($positionNames[$pid] ?? '');
                        if (str_contains($n, 'keep') || str_contains($n, 'goal') || str_contains($n, 'doel')) {
                            $keepers->push($p);
                        } elseif (str_contains($n, 'aanv')) {
                            $attackers->push($p);
                        } elseif (str_contains($n, 'midden')) {
                            $midfielders->push($p);
                        } elseif (str_contains($n, 'verded')) {
                            $defenders->push($p);
                        } else {
                            // Fallback: treat unknown outfield roles as midfielders
                            $midfielders->push($p);
                        }
                    }
                @endphp
                <div class="border rounded p-3 line-up-block">
                    <div class="text-sm font-medium mb-2">Q{{ $q }}</div>
                    {{-- Simple vertical lines: Aanvaller (top), Middenvelder, Verdediger, Keeper (bottom) --}}
                    <div class="flex flex-col gap-4 sm:gap-6 bg-emerald-50 p-3 rounded line-up-player-rows">
                        {{-- Aanvallers --}}
                        <div class="flex flex-wrap justify-center gap-2 sm:gap-16 min-h-6">
                            @foreach($attackers as $p)
                                <span class="inline-block px-2 py-1 rounded text-white bg-green-600 text-sm">{{ $p->name }}</span>
                            @endforeach
                        </div>
                        {{-- Middenvelders --}}
                        <div class="flex flex-wrap justify-center gap-2 sm:gap-16 min-h-6">
                            @foreach($midfielders as $p)
                                <span class="inline-block px-2 py-1 rounded text-white bg-green-600 text-sm">{{ $p->name }}</span>
                            @endforeach
                        </div>
                        {{-- Verdedigers --}}
                        <div class="flex flex-wrap justify-center gap-2 sm:gap-16 min-h-6">
                            @foreach($defenders as $p)
                                <span class="inline-block px-2 py-1 rounded text-white bg-green-600 text-sm">{{ $p->name }}</span>
                            @endforeach
                        </div>
                        {{-- Keeper(s) --}}
                        <div class="flex flex-wrap justify-center gap-2 sm:gap-16 min-h-6">
                            @foreach($keepers as $p)
                                <span class="inline-block px-2 py-1 rounded text-white bg-green-800 text-sm">{{ $p->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Spelers overzicht met kwarten gespeeld --}}
    <div class="mt-6 bg-white p-4 shadow rounded player-stats">
        <h2 class="text-xl font-semibold mb-3">Statistieken</h2>
        <div class="overflow-x-auto">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                    <tr class="text-left text-gray-600 border-b">
                        <th class="py-2 pr-4">Speler</th>
                        <th class="py-2 pr-4">Kwarten gespeeld</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($playersWithQuarters as $item)
                        <tr class="border-t">
                            <td class="py-2 pr-4 font-medium">{{ $item['player']->name }}</td>
                            <td class="py-2 pr-4">
                                @if($item['quarters_played'] > 0)
                                    <span class="inline-block px-2 py-1 rounded text-white bg-green-600">{{ $item['quarters_played'] }}</span>
                                @else
                                    <span class="inline-block px-2 py-1 rounded bg-red-200 text-red-800">Afwezig</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
