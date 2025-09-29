@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-semibold mb-4">Edit Opponent</h1>
<form action="{{ route('opponents.update', $opponent) }}" method="POST" class="bg-white p-4 shadow rounded max-w-lg">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label class="block text-sm font-medium mb-1">Name</label>
        <input type="text" name="name" value="{{ old('name', $opponent->name) }}" class="w-full border rounded p-2" required>
    </div>
    <div class="mb-3">
        <label class="block text-sm font-medium mb-1">Location</label>
        <input type="text" name="location" value="{{ old('location', $opponent->location) }}" class="w-full border rounded p-2" required>
    </div>
    <div class="mb-3">
        <label class="block text-sm font-medium mb-1">Logo URL</label>
        <input type="text" name="logo" value="{{ old('logo', $opponent->logo) }}" class="w-full border rounded p-2">
    </div>
    <div class="mb-3 grid grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium mb-1">Latitude</label>
            <input type="number" step="any" name="latitude" value="{{ old('latitude', $opponent->latitude) }}" class="w-full border rounded p-2" required>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Longitude</label>
            <input type="number" step="any" name="longitude" value="{{ old('longitude', $opponent->longitude) }}" class="w-full border rounded p-2" required>
        </div>
    </div>
    <div class="flex gap-2">
        <button class="px-3 py-2 bg-blue-600 text-white rounded">Update</button>
        <a href="{{ route('opponents.show', $opponent) }}" class="px-3 py-2 bg-gray-200 rounded">Cancel</a>
    </div>
</form>
@endsection
