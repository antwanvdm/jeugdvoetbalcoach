<x-app-layout>
    <h1 class="text-2xl font-semibold mb-4">Bewerk tegenstander</h1>
    <form action="{{ route('opponents.update', $opponent) }}" method="POST" enctype="multipart/form-data" class="bg-white p-4 shadow rounded max-w-2xl">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Naam</label>
            <input type="text" name="name" value="{{ old('name', $opponent->name) }}" class="w-full border rounded p-2" required>
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Locatie</label>
            <input type="text" name="location" value="{{ old('location', $opponent->location) }}" class="w-full border rounded p-2" required>
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Huidig logo</label>
            @if($opponent->logo)
                <img src="{{ asset('storage/' . $opponent->logo) }}" alt="{{ $opponent->name }} logo" class="h-20 mb-2">
            @else
                <p class="text-gray-500 text-sm mb-2">Geen logo ingesteld</p>
            @endif
            <label class="block text-sm font-medium mb-1">Nieuw logo uploaden (optioneel)</label>
            <input type="file" name="logo_file" accept="image/*" class="w-full border rounded p-2">
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
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Opslaan</button>
            <a href="{{ route('opponents.show', $opponent) }}" class="px-3 py-2 bg-gray-200 rounded">Annuleren</a>
        </div>
    </form>
</x-app-layout>
