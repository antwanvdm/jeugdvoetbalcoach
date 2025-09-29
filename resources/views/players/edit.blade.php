@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-semibold mb-4">Speler bewerken</h1>
<form action="{{ route('players.update', $player) }}" method="POST" class="bg-white p-4 shadow rounded max-w-lg">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label class="block text-sm font-medium mb-1">Naam</label>
        <input type="text" name="name" value="{{ old('name', $player->name) }}" class="w-full border rounded p-2" required>
    </div>
    <div class="mb-3">
        <label class="block text-sm font-medium mb-1">Positie</label>
        <select name="position_id" class="w-full border rounded p-2" required>
            @foreach($positions as $id => $name)
                <option value="{{ $id }}" @selected(old('position_id', $player->position_id)==$id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex gap-2">
        <button class="px-3 py-2 bg-blue-600 text-white rounded">Werk bij</button>
        <a href="{{ route('players.show', $player) }}" class="px-3 py-2 bg-gray-200 rounded">Annuleer</a>
    </div>
</form>
@endsection
