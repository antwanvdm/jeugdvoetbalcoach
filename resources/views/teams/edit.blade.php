<x-app-layout>
    <h1 class="text-2xl font-semibold mb-4">Team Bewerken</h1>
    <form action="{{ route('teams.update', $team) }}" method="POST" enctype="multipart/form-data" class="bg-white p-4 shadow rounded max-w-lg">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Team Naam</label>
            <input type="text" name="name" value="{{ old('name', $team->name) }}" class="w-full border rounded p-2" required>
            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Logo</label>
            @if($team->logo)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $team->logo) }}" alt="{{ $team->name }}" class="h-20 w-20 object-contain">
                    <p class="text-xs text-gray-600 mt-1">Huidig logo</p>
                </div>
            @endif
            <input type="file" name="logo" accept="image/*" class="w-full border rounded p-2">
            <p class="text-xs text-gray-600 mt-1">Laat leeg om het huidige logo te behouden</p>
            @error('logo')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Locatie</label>
            <input type="text" name="maps_location" value="{{ old('maps_location', $team->maps_location) }}" class="w-full border rounded p-2">
            @error('maps_location')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="flex gap-2">
            <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded">Opslaan</button>
            <a href="{{ route('teams.index') }}" class="px-3 py-2 bg-gray-200 rounded">Annuleren</a>
        </div>
    </form>

    <div class="bg-white p-4 shadow rounded max-w-lg mt-6">
        <h2 class="text-lg font-medium mb-2">Uitnodigingscode</h2>
        <p class="text-sm text-gray-600 mb-3">Deel deze link of code om coaches uit te nodigen voor dit team.</p>

        <div class="mb-2 flex items-center gap-2">
            <input type="text" readonly class="flex-1 border rounded p-2 bg-gray-50" value="{{ $team->invite_code }}">
            <button 
                type="button" 
                data-copy-to-clipboard="{{ $team->invite_code }}"
                data-copy-message="Uitnodigingscode gekopieerd!"
                class="px-3 py-2 bg-blue-600 text-white rounded"
            >
                Kopieer code
            </button>
        </div>
        <div class="mb-4 text-xs text-gray-600">
            Link: <span class="font-mono">{{ route('teams.join.show', $team->invite_code) }}</span>
            <button 
                type="button" 
                data-copy-to-clipboard="{{ route('teams.join.show', $team->invite_code) }}"
                data-copy-message="Uitnodigingslink gekopieerd!"
                class="ml-2 text-blue-600 hover:underline"
            >
                Kopieer link
            </button>
        </div>

        <form action="{{ route('teams.invite.regenerate', $team) }}" method="POST" onsubmit="return confirm('Weet je zeker dat je een nieuwe code wilt genereren? De oude link werkt dan niet meer.');">
            @csrf
            <button type="submit" class="text-sm text-red-600 hover:text-red-800 underline">Nieuwe code genereren</button>
        </form>
    </div>
</x-app-layout>
