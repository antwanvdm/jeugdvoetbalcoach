<x-app-layout>
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Welkom bij {{ config('app.name') }}! 🎉</h1>
                <p class="text-gray-600">Laten we je team opzetten in een paar eenvoudige stappen</p>
            </div>

            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-between relative">
                    <!-- Step 1 - Completed -->
                    <div class="flex-1 text-center relative">
                        <div class="w-10 h-10 mx-auto bg-green-500 text-white rounded-full flex items-center justify-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-green-600">Account aangemaakt</p>
                    </div>

                    <!-- Connector Line -->
                    <div class="flex-1 border-t-2 border-gray-300 mx-2 -mt-8"></div>

                    <!-- Step 2 - Current -->
                    <div class="flex-1 text-center relative">
                        <div class="w-10 h-10 mx-auto bg-blue-600 text-white rounded-full flex items-center justify-center mb-2">
                            <span class="font-bold">2</span>
                        </div>
                        <p class="text-sm font-medium text-blue-600">Seizoen aanmaken</p>
                    </div>

                    <!-- Connector Line -->
                    <div class="flex-1 border-t-2 border-gray-300 mx-2 -mt-8"></div>

                    <!-- Step 3 - Upcoming -->
                    <div class="flex-1 text-center relative">
                        <div class="w-10 h-10 mx-auto bg-gray-300 text-gray-600 rounded-full flex items-center justify-center mb-2">
                            <span class="font-bold">3</span>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Spelers toevoegen</p>
                    </div>

                    <!-- Connector Line -->
                    <div class="flex-1 border-t-2 border-gray-300 mx-2 -mt-8"></div>

                    <!-- Step 4 - Upcoming -->
                    <div class="flex-1 text-center relative">
                        <div class="w-10 h-10 mx-auto bg-gray-300 text-gray-600 rounded-full flex items-center justify-center mb-2">
                            <span class="font-bold">4</span>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Wedstrijden plannen</p>
                    </div>
                </div>
            </div>

            <!-- Current Step Details -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-blue-900 mb-3">Stap 2: Maak je eerste seizoen aan</h2>
                <p class="text-blue-800 mb-4">
                    Een seizoen definieert een periode waarin je wedstrijden speelt met een vaste groep spelers. 
                    Bijvoorbeeld "Najaar 2024" of "Voorjaar 2025".
                </p>
                <ul class="space-y-2 text-blue-800">
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
                    <button type="submit" class="w-full px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Later doen
                    </button>
                </form>
                <form action="{{ route('onboarding.complete') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                        Seizoen aanmaken →
                    </button>
                </form>
            </div>

            <p class="text-center text-sm text-gray-500 mt-4">
                Dit duurt nog geen 2 minuten! ⏱️
            </p>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-gray-50 rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-2">💡 Waarom een seizoen?</h3>
            <p class="text-gray-700 text-sm">
                Zonder seizoen kunnen we geen wedstrijden of spelers beheren. Een seizoen vormt de basis voor 
                je teammanagement en zorgt ervoor dat we automatisch slimme line-ups kunnen genereren met 
                eerlijke speeltijdverdeling en keeperrotatie.
            </p>
        </div>
    </div>
</x-app-layout>
