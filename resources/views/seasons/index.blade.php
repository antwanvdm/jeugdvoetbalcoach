<x-app-layout>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Seizoenen</h1>
        <a href="{{ route('seasons.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">Nieuw seizoen</a>
    </div>

    <div class="bg-white p-4 shadow rounded">
        <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
            <tr class="text-left text-gray-600 border-b">
                <th class="py-2 pr-4">Jaar</th>
                <th class="py-2 pr-4">Deel</th>
                <th class="py-2 pr-4">Start</th>
                <th class="py-2 pr-4">Eind</th>
                <th class="py-2 pr-4">Formatie</th>
                <th class="py-2 pr-4"></th>
            </tr>
            </thead>
            <tbody>
            @foreach($seasons as $season)
                <tr class="border-t">
                    <td class="py-2 pr-4 font-medium">{{ $season->year }}-{{ $season->year + 1 }}</td>
                    <td class="py-2 pr-4">{{ $season->part }}</td>
                    <td class="py-2 pr-4">{{ $season->start->format('Y-m-d') }}</td>
                    <td class="py-2 pr-4">{{ $season->end->format('Y-m-d') }}</td>
                    <td class="py-2 pr-4">{{ $season->formation?->lineup_formation ?? '' }}</td>
                    <td class="py-2 pr-4 text-right">
                        <a href="{{ route('seasons.show', $season) }}" class="text-blue-600 mr-2">Bekijk</a>
                        <a href="{{ route('seasons.edit', $season) }}" class="text-yellow-600 mr-2">Bewerk</a>
                        <form action="{{ route('seasons.destroy', $season) }}" method="POST" class="inline" onsubmit="return confirm('Verwijder seizoen?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600">Verwijder</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>

        <div class="mt-4">{{ $seasons->links() }}</div>
    </div>
</x-app-layout>
