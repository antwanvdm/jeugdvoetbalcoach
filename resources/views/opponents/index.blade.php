<x-app-layout>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Tegenstanders</h1>
        <a href="{{ route('opponents.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">Nieuwe tegenstander</a>
    </div>

    <div class="overflow-x-auto">
    <table class="min-w-full bg-white shadow rounded">
        <thead>
        <tr class="border-b">
            <th class="text-left p-3"></th>
            <th class="text-left p-3">Naam</th>
            <th class="text-left p-3">Locatie</th>
            <th class="text-right p-3"></th>
        </tr>
        </thead>
        <tbody>
        @forelse($opponents as $opponent)
            <tr class="border-b">
                <td class="p-3"><img src="{{ asset('storage/' . $opponent->logo) }}" alt="{{ $opponent->name }} logo" class="h-8"></td>
                <td class="p-3">{{ $opponent->name }}</td>
                <td class="p-3">
                    <a href="{{ $opponent->location_maps_link }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">{{ $opponent->location }}</a>
                </td>
                <td class="p-3 text-right">
                    <a class="text-blue-600 mr-2" href="{{ route('opponents.show', $opponent) }}">Bekijk</a>
                    <a class="text-yellow-600 mr-2" href="{{ route('opponents.edit', $opponent) }}">Bewerk</a>
                    <form action="{{ route('opponents.destroy', $opponent) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600" onclick="return confirm('Deze tegenstander verwijderen?')">Verwijder</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="p-3 text-center text-gray-500">Nog geen tegenstanders.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    </div>

    <div class="mt-4">{{ $opponents->links() }}</div>
</x-app-layout>
