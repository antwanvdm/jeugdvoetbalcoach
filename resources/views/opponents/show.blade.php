@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Tegenstander: {{ $opponent->name }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('opponents.index') }}" class="px-3 py-2 bg-gray-200 rounded">Terug</a>
            <a href="{{ route('opponents.edit', $opponent) }}" class="px-3 py-2 bg-yellow-500 text-white rounded">Bewerk</a>
            <form action="{{ route('opponents.destroy', $opponent) }}" method="POST" onsubmit="return confirm('Deze tegenstander verwijderen?')">
                @csrf
                @method('DELETE')
                <button class="px-3 py-2 bg-red-600 text-white rounded">Verwijder</button>
            </form>
        </div>
    </div>

    <div class="bg-white p-4 shadow rounded max-w-xl">
        <dl class="grid grid-cols-3 gap-2">
            <dt class="font-medium text-gray-600">Naam</dt>
            <dd class="col-span-2">{{ $opponent->name }}</dd>

            <dt class="font-medium text-gray-600">Locatie</dt>
            <dd class="col-span-2">
                @php
                    $hasCoords = !is_null($opponent->latitude) && !is_null($opponent->longitude);
                    $mapsUrl = $hasCoords
                        ? 'https://www.google.com/maps?q=' . urlencode($opponent->latitude . ',' . $opponent->longitude)
                        : ( $opponent->location ? 'https://www.google.com/maps?q=' . urlencode($opponent->location) : null );
                @endphp
                @if($mapsUrl)
                    <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">{{ $opponent->location ?: ($opponent->latitude . ', ' . $opponent->longitude) }}</a>
                @else
                    <span class="text-gray-500">-</span>
                @endif
            </dd>

            <dt class="font-medium text-gray-600">Logo</dt>
            <dd class="col-span-2">
                @if($opponent->logo)
                    <img src="{{ $opponent->logo }}" alt="{{ $opponent->name }} logo" class="h-12">
                @else
                    <span class="text-gray-500">-</span>
                @endif
            </dd>
        </dl>
    </div>
@endsection
