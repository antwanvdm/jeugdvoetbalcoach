@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-semibold mb-4">New Player</h1>
<form action="{{ route('players.store') }}" method="POST" class="bg-white p-4 shadow rounded max-w-lg">
    @csrf
    <div class="mb-3">
        <label class="block text-sm font-medium mb-1">Name</label>
        <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded p-2" required>
    </div>
    <div class="mb-3">
        <label class="block text-sm font-medium mb-1">Position</label>
        <select name="position_id" class="w-full border rounded p-2" required>
            <option value="">Select a position</option>
            @foreach($positions as $id => $name)
                <option value="{{ $id }}" @selected(old('position_id')==$id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex gap-2">
        <button class="px-3 py-2 bg-blue-600 text-white rounded">Save</button>
        <a href="{{ route('players.index') }}" class="px-3 py-2 bg-gray-200 rounded">Cancel</a>
    </div>
</form>
@endsection
