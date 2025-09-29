@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold">Posities</h1>
    <a href="{{ route('positions.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">Nieuwe positie</a>
</div>

<table class="min-w-full bg-white shadow rounded">
    <thead>
        <tr class="border-b">
            <th class="text-left p-3">Naam</th>
            <th class="text-right p-3"></th>
        </tr>
    </thead>
    <tbody>
        @forelse($positions as $position)
            <tr class="border-b">
                <td class="p-3">{{ $position->name }}</td>
                <td class="p-3 text-right">
                    <a class="text-blue-600 mr-2" href="{{ route('positions.show', $position) }}">Bekijk</a>
                    <a class="text-yellow-600 mr-2" href="{{ route('positions.edit', $position) }}">Bewerk</a>
                    <form action="{{ route('positions.destroy', $position) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600" onclick="return confirm('Deze positie verwijderen?')">Verwijder</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="p-3 text-center text-gray-500">Nog geen posities.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-4">{{ $positions->links() }}</div>
@endsection
