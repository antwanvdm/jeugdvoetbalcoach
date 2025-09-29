@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Player: {{ $player->name }}</h1>
    <div class="flex gap-2">
        <a href="{{ route('players.edit', $player) }}" class="px-3 py-2 bg-yellow-500 text-white rounded">Edit</a>
        <form action="{{ route('players.destroy', $player) }}" method="POST" onsubmit="return confirm('Delete this player?')">
            @csrf
            @method('DELETE')
            <button class="px-3 py-2 bg-red-600 text-white rounded">Delete</button>
        </form>
    </div>
</div>

<div class="bg-white p-4 shadow rounded max-w-lg">
    <div class="mb-2">
        <div class="text-sm text-gray-600">Name</div>
        <div class="font-medium">{{ $player->name }}</div>
    </div>
    <div class="mb-2">
        <div class="text-sm text-gray-600">Position</div>
        <div class="font-medium">{{ $player->position->name ?? '-' }}</div>
    </div>
</div>

<div class="mt-4">
    <a href="{{ route('players.index') }}" class="text-blue-600">Back to list</a>
</div>
@endsection
