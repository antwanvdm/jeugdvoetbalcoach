<x-app-layout>
    @if($onboardingInProgress)
        <div class="mb-4 p-3 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-between">
            <div class="text-sm text-blue-900">
                <strong>Teamsetup nog niet afgerond</strong> — Voltooi alle stappen om aan de slag te gaan.
            </div>
            <a href="{{ route('dashboard') }}" class="text-xs px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 whitespace-nowrap ml-2">Ga verder →</a>
        </div>
    @endif

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
                <th class="py-2 pr-4">Fase</th>
                <th class="py-2 pr-4 hidden sm:table-cell">Start</th>
                <th class="py-2 pr-4 hidden sm:table-cell">Eind</th>
                <th class="py-2 pr-4">Formatie</th>
                <th class="py-2 pr-4"></th>
            </tr>
            </thead>
            <tbody>
            @foreach($seasons as $season)
                <tr class="border-t">
                    <td class="py-2 pr-4 font-medium">{{ $season->year }}-{{ $season->year + 1 }}</td>
                    <td class="py-2 pr-4">{{ $season->part }}</td>
                    <td class="py-2 pr-4 hidden sm:table-cell">{{ $season->start->format('Y-m-d') }}</td>
                    <td class="py-2 pr-4 hidden sm:table-cell">{{ $season->end->format('Y-m-d') }}</td>
                    <td class="py-2 pr-4">{{ $season->formation?->lineup_formation ?? '' }}</td>
                    <td class="py-2 pr-4 text-right">
                        <a href="{{ route('seasons.show', $season) }}" class="text-blue-600 mr-2">Bekijk</a>
                        <a href="{{ route('seasons.edit', $season) }}" class="text-yellow-600 mr-2 hidden sm:inline">Bewerk</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        </div>

        <div class="mt-4">{{ $seasons->links() }}</div>
    </div>
</x-app-layout>
