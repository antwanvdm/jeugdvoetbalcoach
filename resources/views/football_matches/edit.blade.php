@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-semibold mb-4">Edit Match</h1>
<form action="{{ route('football-matches.update', $footballMatch) }}" method="POST" class="bg-white p-4 shadow rounded max-w-xl">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label class="block text-sm font-medium mb-1">Opponent</label>
        <select name="opponent_id" class="w-full border rounded p-2" required>
            @foreach($opponents as $id => $name)
                <option value="{{ $id }}" @selected(old('opponent_id', $footballMatch->opponent_id)==$id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="block text-sm font-medium mb-1">Venue</label>
        <div class="flex items-center gap-6">
            <label class="inline-flex items-center gap-2">
                <input type="radio" name="home" value="1" @checked(old('home', (string)(int)$footballMatch->home)==='1')> Home
            </label>
            <label class="inline-flex items-center gap-2">
                <input type="radio" name="home" value="0" @checked(old('home', (string)(int)$footballMatch->home)==='0')> Away
            </label>
        </div>
    </div>

    <div class="mb-3 grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium mb-1">Goals Scored</label>
            <input type="number" name="goals_scores" value="{{ old('goals_scores', $footballMatch->goals_scores) }}" class="w-full border rounded p-2" min="0">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Goals Conceded</label>
            <input type="number" name="goals_conceded" value="{{ old('goals_conceded', $footballMatch->goals_conceded) }}" class="w-full border rounded p-2" min="0">
        </div>
    </div>

    <div class="mb-3">
        <label class="block text-sm font-medium mb-1">Date and Time</label>
        <input type="datetime-local" name="date" value="{{ old('date', optional($footballMatch->date)->format('Y-m-d\TH:i')) }}" class="w-full border rounded p-2" required>
    </div>

    <div class="flex gap-2">
        <button class="px-3 py-2 bg-blue-600 text-white rounded">Update</button>
        <a href="{{ route('football-matches.show', $footballMatch) }}" class="px-3 py-2 bg-gray-200 rounded">Cancel</a>
    </div>
</form>
@endsection
