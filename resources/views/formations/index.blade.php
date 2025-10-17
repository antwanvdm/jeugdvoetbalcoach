
@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Formaties</h1>
        <a href="{{ route('formations.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">Nieuwe formatie</a>
    </div>

    <div class="bg-white p-4 shadow rounded">
        <table class="min-w-full text-sm">
            <thead>
            <tr class="text-left text-gray-600 border-b">
                <th class="py-2 pr-4">Spelers</th>
                <th class="py-2 pr-4">Opstelling</th>
                <th class="py-2 pr-4"></th>
            </tr>
            </thead>
            <tbody>
            @foreach($formations as $f)
                <tr class="border-t">
                    <td class="py-2 pr-4 font-medium">{{ $f->total_players }}</td>
                    <td class="py-2 pr-4">{{ $f->lineup_formation }}</td>
                    <td class="py-2 pr-4 text-right">
                        <a href="{{ route('formations.show', $f) }}" class="text-blue-600 mr-2">Bekijk</a>
                        <a href="{{ route('formations.edit', $f) }}" class="text-yellow-600 mr-2">Bewerk</a>
                        <form action="{{ route('formations.destroy', $f) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600" onclick="return confirm('Formatie verwijderen?')">Verwijder</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="mt-4">{{ $formations->links() }}</div>
    </div>
@endsection
