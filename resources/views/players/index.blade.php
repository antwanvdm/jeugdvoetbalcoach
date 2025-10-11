@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Spelers</h1>
        <div class="flex items-center gap-4">
            <form method="GET" action="{{ route('players.index') }}">
                <select name="season_id" onchange="this.form.submit()" class="border p-2 rounded">
                    <option value="">Alle seizoenen</option>
                    @foreach($seasons as $s)
                        <option value="{{ $s->id }}" {{ (int)($seasonId ?? 0) === $s->id ? 'selected' : '' }}>{{ $s->year }}-{{ $s->part }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('players.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">Nieuwe speler</a>
        </div>
    </div>

    <table class="min-w-full bg-white shadow rounded">
        <thead>
        <tr class="border-b">
            <th class="text-left p-3">Naam</th>
            <th class="text-left p-3">Favoriete positie</th>
            <th class="text-left p-3">Fysiek</th>
            <th class="text-left p-3">Keer gekeept</th>
            <th class="text-right p-3"></th>
        </tr>
        </thead>
        <tbody>
        @forelse($players as $player)
            <tr class="border-b">
                <td class="p-3">{{ $player->name }}</td>
                <td class="p-3">{{ $player->position->name ?? '-' }}</td>
                <td class="p-3">{{ $player->weight }}</td>
                <td class="p-3">{{ $player->keeper_count ?? 0 }}</td>
                <td class="p-3 text-right">
                    <a class="text-blue-600 mr-2" href="{{ route('players.show', $player) }}">Bekijk</a>
                    <a class="text-yellow-600 mr-2" href="{{ route('players.edit', $player) }}">Bewerk</a>
                    <form action="{{ route('players.destroy', $player) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600" onclick="return confirm('Deze speler verwijderen?')">Verwijder</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="p-3 text-center text-gray-500">Voeg eerst spelers toe.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $players->links() }}</div>
@endsection
