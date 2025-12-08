<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 top-row-actions">
        <h1 class="text-2xl font-semibold">Positie: {{ $position->name }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.positions.index') }}" class="px-3 py-2 bg-gray-200 rounded">Terug</a>
            <a href="{{ route('admin.positions.edit', $position) }}" class="px-3 py-2 bg-yellow-500 text-white rounded">Bewerk</a>
            <form action="{{ route('admin.positions.destroy', $position) }}" method="POST" onsubmit="return confirm('Deze positie verwijderen?')">
                @csrf
                @method('DELETE')
                <button class="px-3 py-2 bg-red-600 text-white rounded">Verwijder</button>
            </form>
        </div>
    </div>

    <div class="bg-white p-4 shadow rounded max-w-2xl">
        <div class="mb-2">
            <div class="text-sm text-gray-600">Naam</div>
            <div class="font-medium">{{ $position->name }}</div>
        </div>
    </div>
</x-app-layout>
