@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Tegenstanders</h1>
    <a href="{{ route('opponents.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">Nieuwe tegenstander</a>
</div>

<table class="min-w-full bg-white shadow rounded">
    <thead>
        <tr class="border-b">
            <th class="text-left p-3">Naam</th>
            <th class="text-left p-3">Locatie</th>
            <th class="text-right p-3"></th>
        </tr>
    </thead>
    <tbody>
        @forelse($opponents as $opponent)
            <tr class="border-b">
                <td class="p-3">{{ $opponent->name }}</td>
                <td class="p-3">
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
                </td>
                <td class="p-3 text-right">
                    <a class="text-blue-600 mr-2" href="{{ route('opponents.show', $opponent) }}">Toon</a>
                    <a class="text-yellow-600 mr-2" href="{{ route('opponents.edit', $opponent) }}">Bewerk</a>
                    <form action="{{ route('opponents.destroy', $opponent) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600" onclick="return confirm('Deze tegenstander verwijderen?')">Verwijder</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="p-3 text-center text-gray-500">Nog geen tegenstanders.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-4">{{ $opponents->links() }}</div>
@endsection
