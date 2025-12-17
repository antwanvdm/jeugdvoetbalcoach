<x-app-layout>
    <h1 class="text-2xl font-semibold mb-4">Nieuwe positie</h1>
    <form action="{{ route('admin.positions.store') }}" method="POST" class="bg-white dark:bg-gray-800 p-4 shadow dark:shadow-gray-700 rounded max-w-2xl">
        @csrf
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Naam</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded p-2" required>
        </div>
        <div class="flex gap-2">
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Opslaan</button>
            <a href="{{ route('admin.positions.index') }}" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 rounded">Annuleren</a>
        </div>
    </form>
</x-app-layout>
