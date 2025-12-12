<x-app-layout>
    <div class="flex flex-col sm:flex-row gap-4 sm:items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Wedstrijden</h1>
        <div class="flex items-center gap-4">
            <form method="GET" action="{{ route('football-matches.index') }}">
                <select name="season_id" onchange="this.form.submit()" class="border py-2 pl-2 pr-8 rounded">
                    <option value="all">Alle seizoenen</option>
                    @foreach($seasons as $s)
                        <option value="{{ $s->id }}" {{ (int)($seasonId ?? 0) === $s->id ? 'selected' : '' }}>{{ $s->year }}/{{ $s->year + 1 }} - Fase {{ $s->part }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('football-matches.create', ['season_id' => $seasonId !== 'all' ? $seasonId : ($activeSeason?->id ?? '')]) }}" class="px-3 py-2 bg-blue-600 text-white rounded">Plan volgende wedstrijd</a>
        </div>
    </div>

    <div class="overflow-x-auto">
    <table class="min-w-full bg-white shadow rounded">
        <thead>
        <tr class="border-b">
            <th class="text-left p-3">Datum</th>
            <th class="text-left p-3">Tegenstander</th>
            <th class="text-left p-3 hidden sm:table-cell">Locatie</th>
            <th class="text-left p-3">Uitslag</th>
            <th class="text-right p-3"></th>
        </tr>
        </thead>
        <tbody>
        @forelse($footballMatches as $match)
            <tr class="border-b">
                <td class="p-3">{{ $match->date?->translatedFormat('d-m-Y H:i') }}</td>
                <td class="p-3">{{ $match->opponent->name }}</td>
                <td class="p-3 hidden sm:table-cell">{{ $match->home ? 'Thuis' : 'Uit' }}</td>
                <td class="p-3 font-bold result-{{$match->result}}">
                    @if($match->result !== 'O')
                        @if($match->home)
                            {{ $match->goals_scored }} - {{ $match->goals_conceded }}
                        @else
                            {{ $match->goals_conceded }} - {{ $match->goals_scored }}
                        @endif
                    @else
                        <span class="text-gray-500">-</span>
                    @endif
                </td>
                <td class="p-3 text-right">
                    <a class="text-blue-600 mr-2" href="{{ route('football-matches.show', $match) }}">Bekijk</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="p-3 text-center text-gray-500">Nog geen wedstrijden.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    </div>

    <div class="mt-4">{{ $footballMatches->links() }}</div>
</x-app-layout>
