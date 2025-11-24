<x-app-layout>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-semibold mb-6">Wedstrijden</h1>

        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z"/>
                </svg>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 mb-3">Nog geen spelers toegevoegd</h2>
            <p class="text-gray-600 mb-6">
                Om een wedstrijd te kunnen plannen heb je minimaal één speler nodig.
                Voeg eerst spelers toe aan je team.
            </p>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-left">
                <p class="text-blue-900 font-medium mb-2">📋 Wat heb je nodig?</p>
                <ul class="text-blue-800 text-sm space-y-1">
                    <li>• Minstens één speler in je team</li>
                    <li>• Een actieve formatie via een seizoen</li>
                </ul>
            </div>

            <div class="flex gap-4 justify-center">
                <a href="{{ route('dashboard') }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Terug naar dashboard
                </a>
                <a href="{{ route('players.create') }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                    Voeg je eerste speler toe
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
