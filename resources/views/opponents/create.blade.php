<x-app-layout>
    <h1 class="text-2xl font-semibold mb-4">Nieuwe tegenstander</h1>
    <form action="{{ route('admin.opponents.store') }}" method="POST" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 p-4 shadow dark:shadow-gray-700 rounded max-w-2xl">
        @csrf
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Naam</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" required>
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Locatie</label>
            <input type="text" name="location" value="{{ old('location') }}" class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" required>
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Echte naam (Real name)</label>
            <input type="text" name="real_name" value="{{ old('real_name') }}" class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Adres</label>
            <input type="text" name="address" value="{{ old('address') }}" class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Website</label>
            <input type="url" name="website" value="{{ old('website') }}" class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium mb-1">Logo upload</label>
            <input type="file" name="logo_file" accept="image/*" class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" required>
        </div>
        <div class="mb-3 grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium mb-1">Latitude</label>
                <input type="number" step="any" name="latitude" value="{{ old('latitude') }}" class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Longitude</label>
                <input type="number" step="any" name="longitude" value="{{ old('longitude') }}" class="w-full border rounded p-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" required>
            </div>
        </div>
        <div class="flex gap-2">
            <button class="px-3 py-2 bg-blue-600 text-white rounded">Opslaan</button>
            <a href="{{ route('admin.opponents.index') }}" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 rounded">Annuleren</a>
        </div>
    </form>
</x-app-layout>
