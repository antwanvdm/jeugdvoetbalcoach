@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-semibold mb-4">Edit Position</h1>
<form action="{{ route('positions.update', $position) }}" method="POST" class="bg-white p-4 shadow rounded max-w-lg">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label class="block text-sm font-medium mb-1">Name</label>
        <input type="text" name="name" value="{{ old('name', $position->name) }}" class="w-full border rounded p-2" required>
    </div>
    <div class="flex gap-2">
        <button class="px-3 py-2 bg-blue-600 text-white rounded">Update</button>
        <a href="{{ route('positions.show', $position) }}" class="px-3 py-2 bg-gray-200 rounded">Cancel</a>
    </div>
</form>
@endsection
