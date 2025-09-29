@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Football Matches</h1>
    <a href="{{ route('football-matches.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">New Match</a>
</div>

<table class="min-w-full bg-white shadow rounded">
    <thead>
        <tr class="border-b">
            <th class="text-left p-3">Date</th>
            <th class="text-left p-3">Opponent</th>
            <th class="text-left p-3">Venue</th>
            <th class="text-left p-3">Score</th>
            <th class="text-right p-3">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($footballMatches as $match)
            <tr class="border-b">
                <td class="p-3">{{ $match->date?->format('Y-m-d H:i') }}</td>
                <td class="p-3">{{ $match->opponent->name ?? '-' }}</td>
                <td class="p-3">{{ $match->home ? 'Home' : 'Away' }}</td>
                <td class="p-3">
                    @if(!is_null($match->goals_scores) && !is_null($match->goals_conceded))
                        {{ $match->goals_scores }} - {{ $match->goals_conceded }}
                    @else
                        <span class="text-gray-500">-</span>
                    @endif
                </td>
                <td class="p-3 text-right">
                    <a class="text-blue-600 mr-2" href="{{ route('football-matches.show', $match) }}">Show</a>
                    <a class="text-blue-600 mr-2" href="{{ route('football-matches.lineup', $match) }}">Lineup</a>
                    <a class="text-yellow-600 mr-2" href="{{ route('football-matches.edit', $match) }}">Edit</a>
                    <form action="{{ route('football-matches.destroy', $match) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600" onclick="return confirm('Delete this match?')">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="p-3 text-center text-gray-500">No matches yet.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-4">{{ $footballMatches->links() }}</div>
@endsection
