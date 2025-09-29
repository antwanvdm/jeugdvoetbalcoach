@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Match vs {{ $footballMatch->opponent->name ?? 'Unknown' }}</h1>
    <div class="flex gap-2">
        <a href="{{ route('football-matches.edit', $footballMatch) }}" class="px-3 py-2 bg-yellow-500 text-white rounded">Edit</a>
        <form action="{{ route('football-matches.destroy', $footballMatch) }}" method="POST" onsubmit="return confirm('Delete this match?')">
            @csrf
            @method('DELETE')
            <button class="px-3 py-2 bg-red-600 text-white rounded">Delete</button>
        </form>
    </div>
</div>

<div class="bg-white p-4 shadow rounded max-w-xl">
    <dl class="grid grid-cols-3 gap-2">
        <dt class="font-medium text-gray-600">Opponent</dt>
        <dd class="col-span-2">{{ $footballMatch->opponent->name ?? '-' }}</dd>

        <dt class="font-medium text-gray-600">Venue</dt>
        <dd class="col-span-2">{{ $footballMatch->home ? 'Home' : 'Away' }}</dd>

        <dt class="font-medium text-gray-600">Date</dt>
        <dd class="col-span-2">{{ $footballMatch->date?->format('Y-m-d H:i') }}</dd>

        <dt class="font-medium text-gray-600">Score</dt>
        <dd class="col-span-2">
            @if(!is_null($footballMatch->goals_scores) && !is_null($footballMatch->goals_conceded))
                {{ $footballMatch->goals_scores }} - {{ $footballMatch->goals_conceded }}
            @else
                <span class="text-gray-500">-</span>
            @endif
        </dd>
    </dl>
</div>

<div class="mt-4">
    <a href="{{ route('football-matches.index') }}" class="px-3 py-2 bg-gray-200 rounded">Back</a>
</div>
@endsection
