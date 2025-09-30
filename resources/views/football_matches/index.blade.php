@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Wedstrijden</h1>
        <a href="{{ route('football-matches.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">Nieuwe wedstrijd</a>
    </div>

    <table class="min-w-full bg-white shadow rounded">
        <thead>
        <tr class="border-b">
            <th class="text-left p-3">Datum</th>
            <th class="text-left p-3">Tegenstander</th>
            <th class="text-left p-3">Locatie</th>
            <th class="text-left p-3">Uitslag</th>
            <th class="text-right p-3"></th>
        </tr>
        </thead>
        <tbody>
        @forelse($footballMatches as $match)
            <tr class="border-b">
                <td class="p-3">{{ $match->date?->translatedFormat('d-m-Y H:i') }}</td>
                <td class="p-3">{{ $match->opponent->name ?? '-' }}</td>
                <td class="p-3">{{ $match->home ? 'Thuis' : 'Uit' }}</td>
                <td class="p-3">
                    @if(!is_null($match->goals_scores) && !is_null($match->goals_conceded))
                        {{ $match->goals_scores }} - {{ $match->goals_conceded }}
                    @else
                        <span class="text-gray-500">-</span>
                    @endif
                </td>
                <td class="p-3 text-right">
                    <a class="text-blue-600 mr-2" href="{{ route('football-matches.show', $match) }}">Bekijk</a>
                    <a class="text-blue-600 mr-2" href="{{ route('football-matches.lineup', $match) }}">Line-up</a>
                    <a class="text-yellow-600 mr-2" href="{{ route('football-matches.edit', $match) }}">Bewerk</a>
                    <form action="{{ route('football-matches.destroy', $match) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600" onclick="return confirm('Deze wedstrijd verwijderen?')">Verwijder</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="p-3 text-center text-gray-500">Nog geen wedstrijden.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $footballMatches->links() }}</div>
@endsection
