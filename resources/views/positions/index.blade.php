<x-app-layout>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Posities</h1>
        <a href="{{ route('admin.positions.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">Nieuwe positie</a>
    </div>

    <div class="overflow-x-auto">
    <table class="min-w-full bg-white dark:bg-gray-800 shadow dark:shadow-gray-700 rounded">
        <thead>
        <tr class="border-b">
            <th class="text-left p-3">Naam</th>
            <th class="text-right p-3"></th>
        </tr>
        </thead>
        <tbody>
        @forelse($positions as $position)
            <tr class="border-b">
                <td class="p-3">{{ $position->name }}</td>
                <td class="p-3 text-right">
                    <a class="text-blue-600 dark:text-blue-400 mr-2" href="{{ route('admin.positions.show', $position) }}">Bekijk</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="p-3 text-center text-gray-500 dark:text-gray-400">Nog geen posities.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    </div>

    <div class="mt-4">{{ $positions->links() }}</div>
</x-app-layout>
