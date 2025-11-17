<x-app-layout>
    <h1 class="text-2xl font-semibold mb-4">Nieuw Team</h1>
    <form action="{{ route('teams.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-4 shadow rounded max-w-lg">
        @csrf
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Team Naam</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded p-2" required>
            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Logo (optioneel)</label>
            <input type="file" name="logo" accept="image/*" class="w-full border rounded p-2">
            @error('logo')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Locatie (optioneel)</label>
            <input type="text" name="maps_location" value="{{ old('maps_location') }}" class="w-full border rounded p-2">
            @error('maps_location')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="flex gap-2">
            <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Opslaan</button>
            <a href="{{ route('teams.index') }}" class="px-3 py-2 bg-gray-200 rounded">Annuleren</a>
        </div>
    </form>
</x-app-layout>
