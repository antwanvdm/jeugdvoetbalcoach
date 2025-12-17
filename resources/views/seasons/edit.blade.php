<x-app-layout>
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-semibold mb-4">Seizoen bewerken</h1>

        <div class="bg-white dark:bg-gray-800 p-4 shadow dark:shadow-gray-700 rounded">
            <form action="{{ route('seasons.update', $season) }}" method="POST">
                @csrf
                @method('PUT')

                @include('seasons._form')

                <div class="mt-4">
                    <button class="px-4 py-2 bg-yellow-600 text-white rounded">Bijwerken</button>
                    <a href="{{ route('seasons.index') }}" class="ml-2 text-gray-600 dark:text-gray-300">Annuleer</a>
                </div>
            </form>
        </div>

        <!-- Share Token Management -->
        <div class="bg-white dark:bg-gray-800 p-4 shadow dark:shadow-gray-700 rounded mt-6">
            <h2 class="text-lg font-semibold mb-3">Seizoen delen met ouders</h2>
            @if($season->share_token)
                <div class="mb-4">
                    <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Deellink</label>
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            readonly
                            value="{{ route('seasons.share', ['season' => $season, 'shareToken' => $season->share_token]) }}"
                            class="flex-1 border rounded p-2 bg-gray-50 dark:bg-gray-900 text-sm"
                        >
                        <button
                            type="button"
                            data-copy-to-clipboard="{{ route('seasons.share', ['season' => $season, 'shareToken' => $season->share_token]) }}"
                            data-copy-message="Deellink gekopieerd!"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-600 cursor-pointer"
                        >
                            📋
                        </button>
                    </div>
                </div>
                <form method="POST" action="{{ route('seasons.share.regenerate', $season) }}" onsubmit="return confirm('Nieuwe link genereren? De oude link werkt dan niet meer.');">
                    @csrf
                    <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:text-red-200 underline">
                        🔄 Nieuwe deellink genereren
                    </button>
                </form>
            @else
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">Nog geen deellink. Genereer een link om dit seizoen met ouders te delen.</p>
                <form method="POST" action="{{ route('seasons.share.regenerate', $season) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Deellink genereren
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
