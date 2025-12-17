<x-app-layout>
    <div class="max-w-3xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg dark:shadow-gray-700 p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">Welkom bij {{ config('app.name') }}! 🎉</h1>
                <p class="text-gray-600 dark:text-gray-300">Laten we je team opzetten in een paar eenvoudige stappen</p>
            </div>

            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-between relative">
                    <!-- Step 2 - Current -->
                    <div class="flex-1 text-center relative">
                        <div class="w-10 h-10 mx-auto bg-blue-600 text-white rounded-full flex items-center justify-center mb-2">
                            <span class="font-bold">1</span>
                        </div>
                        <p class="text-sm font-medium text-blue-600">Seizoen aanmaken</p>
                    </div>

                    <!-- Connector Line -->
                    <div class="flex-1 border-t-2 border-gray-300 dark:border-gray-600 mx-2 -mt-8"></div>

                    <!-- Step 3 - Upcoming -->
                    <div class="flex-1 text-center relative">
                        <div class="w-10 h-10 mx-auto bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-full flex items-center justify-center mb-2">
                            <span class="font-bold">2</span>
                        </div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Spelers toevoegen</p>
                    </div>

                    <!-- Connector Line -->
                    <div class="flex-1 border-t-2 border-gray-300 dark:border-gray-600 mx-2 -mt-8"></div>

                    <!-- Step 4 - Upcoming -->
                    <div class="flex-1 text-center relative">
                        <div class="w-10 h-10 mx-auto bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-full flex items-center justify-center mb-2">
                            <span class="font-bold">3</span>
                        </div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Wedstrijden plannen</p>
                    </div>
                </div>
            </div>

            <!-- Current Step Details -->
            <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-blue-900 dark:text-blue-50 mb-3">Stap 2: Maak je eerste seizoen aan</h2>
                <p class="text-blue-800 dark:text-blue-300 mb-4">
                    Een seizoen definieert een periode waarin je wedstrijden speelt met een vaste groep spelers.
                    Bijvoorbeeld "2025-2026 Fase 3".
                </p>
                <ul class="space-y-2 text-blue-800 dark:text-blue-300">
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Kies een formatie (bijv. 2-1-2 voor 6 spelers)</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Geef een start- en einddatum op</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Je kunt later altijd extra seizoenen aanmaken</span>
                    </li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4">
                <form action="{{ route('onboarding.skip') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 dark:bg-gray-900 transition cursor-pointer">
                        Later doen
                    </button>
                </form>
                <form action="{{ route('onboarding.complete') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition font-semibold cursor-pointer">
                        Seizoen aanmaken →
                    </button>
                </form>
            </div>

            <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-4">
                Dit duurt nog geen 2 minuten! ⏱️
            </p>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">💡 Waarom een seizoen?</h3>
            <p class="text-gray-700 dark:text-gray-200 text-sm">
                Zonder seizoen kunnen we geen wedstrijden of spelers beheren. Een seizoen vormt de basis voor
                je teammanagement en zorgt ervoor dat we automatisch slimme line-ups kunnen genereren met
                eerlijke speeltijdverdeling en keeperrotatie.
            </p>
        </div>
    </div>
</x-app-layout>
