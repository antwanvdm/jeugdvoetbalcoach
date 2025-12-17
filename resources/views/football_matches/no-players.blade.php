<x-app-layout>
    @php
        $minPlayers = $requiredPlayers ?? ($season?->formation?->total_players ?? 1);
    @endphp
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6">Wedstrijden</h1>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg dark:shadow-gray-700 p-8 text-center">
            <div class="mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z"/>
                </svg>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-3">Nog geen spelers toegevoegd</h2>
            <p class="text-gray-600 dark:text-gray-300 mb-6">
                Om een wedstrijd te kunnen plannen heb je minimaal {{ $minPlayers }} spelers nodig volgens de huidige formatie.
                Voeg eerst voldoende spelers toe aan je team.
            </p>

            <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-6 text-left">
                <p class="text-blue-900 dark:text-blue-100 font-medium mb-2">📋 Wat heb je nodig?</p>
                <ul class="text-blue-800 dark:text-blue-300 text-sm space-y-1">
                    <li>• Minstens {{ $minPlayers }} spelers in je team</li>
                    <li>• Een actieve formatie via een seizoen</li>
                </ul>
            </div>

            <div class="flex gap-4 justify-center">
                <a href="{{ route('dashboard') }}" class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 dark:bg-gray-900 transition">
                    Terug naar dashboard
                </a>
                <a href="{{ route('players.create') }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition font-semibold">
                    Voeg je spelers toe
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
