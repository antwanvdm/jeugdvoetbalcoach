<x-app-layout>
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900">Voetbal Team Manager</h1>
        <p class="mt-3 text-gray-600">Professioneel teammanagement voor jeugdvoetbal (JO8 t/m JO12).</p>

        <div class="mt-6 flex items-center justify-center gap-3">
            <a href="{{ route('register') }}" class="px-6 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Registreren</a>
            <a href="{{ route('login') }}" class="px-6 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100">Inloggen</a>
        </div>
    </div>

    <div class="mt-8 space-y-4">
        <h2 class="text-xl font-semibold">Over de app</h2>
        <ul class="text-gray-700 list-disc list-inside space-y-2 text-left">
            <li>Automatische, eerlijke line-ups met rotatie over alle posities (incl. keeper).</li>
            <li>Spelerbeheer met speeltijd en aanwezigheid.</li>
            <li>Seizoenen, wedstrijden en tegenstanders overzichtelijk beheren.</li>
            <li>Formaties: eigen formaties en door admin beheerde globale formaties.</li>
        </ul>
    </div>

    <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-md text-center">
        <p class="text-gray-800 mb-3">Klaar om te starten? Maak gratis een account aan en beheer je team slimmer.</p>
        <a href="{{ route('register') }}" class="inline-block px-6 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Start nu – Gratis</a>
    </div>
</x-app-layout>
