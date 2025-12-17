<x-app-layout>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Formaties</h1>
        @can('create', App\Models\Formation::class)
            <a href="{{ route('formations.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">Nieuwe formatie</a>
        @endcan
    </div>

    <!-- Info Block -->
    <div class="mb-4 p-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 mr-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="text-sm text-green-800 dark:text-green-200">
                <strong class="font-semibold">Formaties:</strong> Een formatie bepaalt hoe spelers verdeeld zijn over het veld (bijv. "2-1-2" = 2 verdedigers, 1 middenvelder, 2 aanvallers). Per seizoensfase kun je een formatie kiezen. Als de reeds
                beschikbare formaties niet voldoen kun je hier je eigen formatie toevoegen.
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-800 p-4 shadow dark:shadow-gray-700 rounded">
            <thead>
            <tr class="text-left text-gray-600 dark:text-gray-300 border-b">
                <th class="p-3"></th>
                <th class="p-3 hidden sm:table-cell">Spelers</th>
                <th class="p-3">Opstelling</th>
                <th class="p-3"></th>
            </tr>
            </thead>
            <tbody>
            @foreach($formations as $f)
                <tr class="border-b">
                    <td class="p-3">
                        @if($f->is_global)
                            <span class="inline-flex items-center text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded">Globale formatie</span>
                        @elseif($f->user)
                            <span class="inline-flex items-center text-xs px-2 py-1 bg-yellow-100 dark:bg-yellow-900 text-gray-900 dark:text-yellow-100 rounded">Eigen formatie</span>
                        @endif
                    </td>
                    <td class="p-3 font-medium hidden sm:table-cell">{{ $f->total_players }}</td>
                    <td class="p-3">{{ $f->lineup_formation }}</td>
                    <td class="p-3 text-right">
                        <a href="{{ route('formations.show', $f) }}" class="text-blue-600 dark:text-blue-400 mr-2">Bekijk</a>
                        @can('update', $f)
                            <a href="{{ route('formations.edit', $f) }}" class="text-yellow-600 mr-2 hidden sm:inline">Bewerk</a>
                        @endcan
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $formations->links() }}</div>
</x-app-layout>
