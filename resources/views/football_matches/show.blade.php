@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Wedstrijd tegen {{ $footballMatch->opponent->name ?? 'Onbekend' }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('football-matches.index') }}" class="px-3 py-2 bg-gray-200 rounded">Terug</a>
            <a href="{{ route('football-matches.lineup', $footballMatch) }}" class="px-3 py-2 bg-indigo-600 text-white rounded">Line-up</a>
            <a href="{{ route('football-matches.edit', $footballMatch) }}" class="px-3 py-2 bg-yellow-500 text-white rounded">Bewerk</a>
            <form action="{{ route('football-matches.destroy', $footballMatch) }}" method="POST" onsubmit="return confirm('Wedstrijd verwijderen?')">
                @csrf
                @method('DELETE')
                <button class="px-3 py-2 bg-red-600 text-white rounded">Verwijder</button>
            </form>
        </div>
    </div>

    <div class="bg-white p-4 shadow rounded max-w-xl">
        <dl class="grid grid-cols-3 gap-2">
            <dt class="font-medium text-gray-600">Tegenstander</dt>
            <dd class="col-span-2">{{ $footballMatch->opponent->name ?? '-' }}</dd>

            <dt class="font-medium text-gray-600">Locatie</dt>
            <dd class="col-span-2">
                @php
                    $label = $footballMatch->home ? 'Thuis' : 'Uit';
                    $homeUrl = 'https://maps.app.goo.gl/oBBodGTYQJxh9iKT7';
                    $mapsUrl = null;
                    $locLabel = null;
                    if ($footballMatch->home) {
                        $mapsUrl = $homeUrl;
                        $locLabel = 'VVOR';
                    } else {
                        $opp = $footballMatch->opponent;
                        $hasCoords = $opp && !is_null($opp->latitude) && !is_null($opp->longitude);
                        $mapsUrl = $hasCoords
                            ? 'https://www.google.com/maps?q=' . urlencode($opp->latitude . ',' . $opp->longitude)
                            : ($opp && $opp->location ? 'https://www.google.com/maps?q=' . urlencode($opp->location) : null);
                        $locLabel = $opp?->location ?: ($hasCoords ? ($opp->latitude . ', ' . $opp->longitude) : null);
                    }
                @endphp
                {{ $label }}
                @if($mapsUrl)
                    (<a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">{{ $locLabel ?? 'bekijk op kaart' }}</a>)
                @endif
            </dd>

            <dt class="font-medium text-gray-600">Datum</dt>
            <dd class="col-span-2">{{ $footballMatch->date?->translatedFormat('j F Y H:i') }}</dd>

            <dt class="font-medium text-gray-600">Uitslag</dt>
            <dd class="col-span-2">
                @if(!is_null($footballMatch->goals_scores) && !is_null($footballMatch->goals_conceded))
                    {{ $footballMatch->goals_scores }} - {{ $footballMatch->goals_conceded }}
                @else
                    <span class="text-gray-500">-</span>
                @endif
            </dd>
        </dl>
    </div>

    {{-- Lineup overview table --}}
    <div class="mt-6 bg-white p-4 shadow rounded">
        <div class="flex justify-between">
            <h2 class="text-xl font-semibold mb-3">Line-up per kwart</h2>
            <a href="{{ route('football-matches.lineup', $footballMatch) }}" class="px-3 py-2 bg-indigo-600 text-white rounded">Bewerk line-up</a>
        </div>
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
                    <tr class="border-t align-top">
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
                                <span class="inline-block mr-1 mb-1 px-2 py-1 rounded text-white bg-green-600">{{ $p->name }}</span>
                            @empty
                                <span class="text-gray-400">-</span>
                            @endforelse
                        </td>
                        <td class="py-2 pr-4">
                            @forelse($bench as $p)
                                <span class="inline-block mr-1 mb-1 px-2 py-1 rounded bg-yellow-300 text-yellow-900">{{ $p->name }}</span>
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

    {{-- Dobbelsteen-opstelling (schematic) per quarter --}}
    <div class="mt-6 bg-white p-4 shadow rounded">
        <h2 class="text-xl font-semibold mb-3">Opstelling per kwart</h2>
        <div class="grid md:grid-cols-2 gap-4">
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
                <div class="border rounded p-3">
                    <div class="text-sm font-medium mb-2">Kwart {{ $q }}</div>
                    {{-- Simple vertical lines: Aanvaller (top), Middenvelder, Verdediger, Keeper (bottom) --}}
                    <div class="flex flex-col gap-6 bg-emerald-50 p-3 rounded">
                        {{-- Aanvallers --}}
                        <div class="flex flex-wrap justify-center gap-16 min-h-6">
                            @foreach($attackers as $p)
                                <span class="inline-block px-2 py-1 rounded text-white bg-green-600">{{ $p->name }}</span>
                            @endforeach
                        </div>
                        {{-- Middenvelders --}}
                        <div class="flex flex-wrap justify-center gap-16 min-h-6">
                            @foreach($midfielders as $p)
                                <span class="inline-block px-2 py-1 rounded text-white bg-green-600">{{ $p->name }}</span>
                            @endforeach
                        </div>
                        {{-- Verdedigers --}}
                        <div class="flex flex-wrap justify-center gap-16 min-h-6">
                            @foreach($defenders as $p)
                                <span class="inline-block px-2 py-1 rounded text-white bg-green-600">{{ $p->name }}</span>
                            @endforeach
                        </div>
                        {{-- Keeper(s) --}}
                        <div class="flex flex-wrap justify-center gap-16 min-h-6">
                            @foreach($keepers as $p)
                                <span class="inline-block px-2 py-1 rounded text-white bg-green-800">{{ $p->name }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

@endsection
