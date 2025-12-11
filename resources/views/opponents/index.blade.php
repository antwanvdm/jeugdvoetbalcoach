<x-app-layout>
    <div class="flex flex-col sm:flex-row gap-4 sm:items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Tegenstanders</h1>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.opponents.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded text-center">Nieuwe tegenstander</a>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow rounded">
            <thead>
            <tr class="border-b">
                <th class="text-left p-3"></th>
                <th class="text-left p-3">Naam</th>
                <th class="text-left p-3 hidden sm:table-cell">Locatie</th>
                @if(auth()->user()->isAdmin())
                    <th class="text-right p-3"></th>
                @endif
            </tr>
            </thead>
            <tbody>
            @forelse($opponents as $opponent)
                <tr class="border-b">
                    <td class="p-3"><img src="{{ asset('storage/' . $opponent->logo) }}" alt="{{ $opponent->name }} logo" class="h-8 min-w-8"></td>
                    <td class="p-3">{{ $opponent->name }}</td>
                    <td class="p-3 hidden sm:table-cell">
                        <a href="{{ $opponent->location_maps_link }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">{{ $opponent->location }}</a>
                    </td>
                    @if(auth()->user()->isAdmin())
                        <td class="p-3 text-right">
                            <a class="text-blue-600 mr-2" href="{{ route('admin.opponents.show', $opponent) }}">Bekijk</a>
                        </td>
                    @endif
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
