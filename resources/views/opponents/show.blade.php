@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Opponent: {{ $opponent->name }}</h1>
    <div class="flex gap-2">
        <a href="{{ route('opponents.edit', $opponent) }}" class="px-3 py-2 bg-yellow-500 text-white rounded">Edit</a>
        <form action="{{ route('opponents.destroy', $opponent) }}" method="POST" onsubmit="return confirm('Delete this opponent?')">
            @csrf
            @method('DELETE')
            <button class="px-3 py-2 bg-red-600 text-white rounded">Delete</button>
        </form>
    </div>
</div>

<div class="bg-white p-4 shadow rounded max-w-xl">
    <dl class="grid grid-cols-3 gap-2">
        <dt class="font-medium text-gray-600">Name</dt>
        <dd class="col-span-2">{{ $opponent->name }}</dd>

        <dt class="font-medium text-gray-600">Location</dt>
        <dd class="col-span-2">{{ $opponent->location }}</dd>

        <dt class="font-medium text-gray-600">Logo</dt>
        <dd class="col-span-2">
            @if($opponent->logo)
                <img src="{{ $opponent->logo }}" alt="{{ $opponent->name }} logo" class="h-12">
            @else
                <span class="text-gray-500">-</span>
            @endif
        </dd>

        <dt class="font-medium text-gray-600">Latitude</dt>
        <dd class="col-span-2">{{ $opponent->latitude }}</dd>

        <dt class="font-medium text-gray-600">Longitude</dt>
        <dd class="col-span-2">{{ $opponent->longitude }}</dd>
    </dl>
</div>

<div class="mt-4">
    <a href="{{ route('opponents.index') }}" class="px-3 py-2 bg-gray-200 rounded">Back</a>
</div>
@endsection
