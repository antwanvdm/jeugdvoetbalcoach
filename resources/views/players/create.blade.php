<x-app-layout>
    <h1 class="text-2xl font-semibold mb-4">Nieuwe speler</h1>
    <form action="{{ route('players.store') }}" method="POST" class="bg-white p-4 shadow rounded max-w-lg">
        @csrf
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Naam</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded p-2" required>
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Positie</label>
            <select name="position_id" class="w-full border rounded p-2" required>
                <option value="">Kies een positie</option>
                @foreach($positions as $id => $name)
                    <option value="{{ $id }}" @selected(old('position_id')==$id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Fysiek</label>
            <input type="number" name="weight" value="{{ old('weight') }}" class="w-full border rounded p-2" required>
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Seizoenen</label>
            <select name="seasons[]" multiple class="w-full border rounded p-2">
                @foreach($seasons as $id => $label)
                    <option value="{{ $id }}" {{ in_array($id, old('seasons', [])) ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <div class="text-sm text-gray-500 mt-1">Houd Cmd/Ctrl ingedrukt om meerdere seizoenen te selecteren.</div>
        </div>
        <div class="flex gap-2">
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Opslaan</button>
            <a href="{{ route('players.index') }}" class="px-3 py-2 bg-gray-200 rounded">Annuleer</a>
        </div>
    </form>
</x-app-layout>
