@push('head')
    <title>Privacy – {{ config('app.name') }}</title>
    <meta name="description" content="Privacy policy voor {{ config('app.name') }}: hoe we omgaan met je gegevens, dataopslag en analytics.">
    <link rel="canonical" href="{{ route('privacy') }}">
    <meta name="robots" content="index,follow">
@endpush
<x-app-layout>
    <section class="mx-auto max-w-4xl px-6 py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Privacy</h1>
        <p class="text-gray-600 mb-8">Laatst bijgewerkt: {{ now()->format('d-m-Y') }}</p>

        <div class="prose prose-blue max-w-none space-y-8">
            <!-- Introductie -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-3">Inleiding</h2>
                <p class="text-gray-700 leading-relaxed">
                    {{ config('app.name') }} respecteert je privacy. Deze pagina legt uit welke gegevens we verzamelen, hoe we deze gebruiken en wat je rechten zijn.
                    We doen ons best om transparant te zijn over dataverwerking en slaan zo min mogelijk gegevens op.
                </p>
            </div>

            <!-- Welke gegevens verzamelen we -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-3">Welke gegevens verzamelen we?</h2>
                <h3 class="text-xl font-semibold text-gray-800 mt-4 mb-2">Account & teamgegevens</h3>
                <p class="text-gray-700 leading-relaxed mb-2">
                    Wanneer je een account aanmaakt, vragen we om:
                </p>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                    <li>Je naam</li>
                    <li>E-mailadres (voor login en verificatie)</li>
                    <li>Wachtwoord (versleuteld opgeslagen)</li>
                </ul>
                <p class="text-gray-700 leading-relaxed mt-2">
                    Daarnaast sla je zelf gegevens op die je invoert in de applicatie:
                </p>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                    <li>Teamnamen en -instellingen</li>
                    <li>Spelersgegevens (namen, nummers, posities, sterkte)</li>
                    <li>Seizoenen, formaties en wedstrijden</li>
                    <li>Opstellingen en line-ups</li>
                </ul>
                <p class="text-gray-700 leading-relaxed mt-2">
                    Al deze gegevens blijven volledig op onze server en worden <strong>enkel door jou en je teamgenoten</strong> gebruikt.
                    We delen deze data niet met derden.
                </p>

                <h3 class="text-xl font-semibold text-gray-800 mt-4 mb-2">Contactformulier (feedback)</h3>
                <p class="text-gray-700 leading-relaxed">
                    Via het feedbackformulier op de homepage kun je ons een bericht sturen. We vragen hierbij om je naam, e-mailadres, onderwerp en bericht.
                    Deze gegevens worden <strong>niet opgeslagen in een database</strong>, maar direct verstuurd naar de e-mailbox van de beheerder en naar jouw eigen e-mailadres (als bevestiging).
                    We gebruiken deze informatie uitsluitend om je vraag te beantwoorden of je feedback te verwerken.
                </p>

                <h3 class="text-xl font-semibold text-gray-800 mt-4 mb-2">Google Analytics (GA4)</h3>
                <p class="text-gray-700 leading-relaxed">
                    We gebruiken Google Analytics om inzicht te krijgen in hoe bezoekers onze website gebruiken. Dit helpt ons de site te verbeteren.
                    Google Analytics verzamelt geanonimiseerde statistieken zoals:
                </p>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                    <li>Paginaweergaven en duur van bezoek</li>
                    <li>Herkomst van bezoekers (bijv. via een zoekmachine)</li>
                    <li>Gebruikt apparaat en browser</li>
                </ul>
                <p class="text-gray-700 leading-relaxed mt-2">
                    <strong>Privacy-vriendelijke instellingen:</strong> We gebruiken Google Analytics met <strong>Consent Mode v2</strong>.
                    Dit betekent dat advertenties en personalisatie standaard zijn <em>uitgeschakeld</em>, maar we wel anonieme statistieken verzamelen om de website te verbeteren.
                    Je IP-adres wordt geanonimiseerd en we delen geen persoonlijke data met Google voor advertentiedoeleinden.
                </p>
                <p class="text-gray-700 leading-relaxed mt-2">
                    Meer informatie over hoe Google met privacy omgaat vind je in het
                    <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">privacybeleid van Google</a>.
                </p>
            </div>

            <!-- Hoe gebruiken we je gegevens -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-3">Hoe gebruiken we je gegevens?</h2>
                <ul class="list-disc list-inside text-gray-700 space-y-2 ml-4">
                    <li><strong>Account & teamgegevens:</strong> om je toegang te geven tot de applicatie, je teams te beheren en opstellingen te genereren.</li>
                    <li><strong>E-mailadres:</strong> voor login, verificatie en eventuele belangrijke mededelingen (bijv. wachtwoord reset).</li>
                    <li><strong>Feedbackformulier:</strong> om je vraag te beantwoorden of feedback te verwerken. We bewaren deze gegevens niet structureel.</li>
                    <li><strong>Google Analytics:</strong> om de website te verbeteren en te begrijpen welke content waardevol is voor gebruikers.</li>
                </ul>
            </div>

            <!-- Bewaartermijn -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-3">Hoe lang bewaren we je gegevens?</h2>
                <p class="text-gray-700 leading-relaxed">
                    We bewaren je accountgegevens en door jou ingevoerde teamdata <strong>zolang je account actief is</strong>.
                </p>
                <p class="text-gray-700 leading-relaxed mt-2">
                    Wanneer je je account verwijdert via je profielpagina, worden <strong>al je persoonlijke gegevens en teamdata direct en permanent verwijderd</strong> van onze server.
                    We houden geen back-ups van verwijderde accounts.
                </p>
                <p class="text-gray-700 leading-relaxed mt-2">
                    Gegevens uit het feedbackformulier blijven mogelijk in de mailbox van de beheerder staan, maar worden niet systematisch bewaard in een database.
                </p>
            </div>

            <!-- Delen met derden -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-3">Delen we je gegevens met derden?</h2>
                <p class="text-gray-700 leading-relaxed">
                    <strong>Nee.</strong> We verkopen of verhuren je gegevens niet. De enige externe diensten die (mogelijk) toegang hebben tot geanonimiseerde data zijn:
                </p>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                    <li><strong>Google Analytics:</strong> voor statistische analyse (alleen met jouw toestemming).</li>
                    <li><strong>Mailgun:</strong> voor het versturen van e-mails (verificatie, wachtwoord reset, feedback). Mailgun verwerkt e-mailadressen enkel om berichten te versturen en bewaart deze niet langer dan nodig.</li>
                    <li><strong>Hostingprovider:</strong> onze server draait bij een hostingpartij. Zij hebben technische toegang tot de server, maar gebruiken je data niet voor eigen doeleinden.</li>
                </ul>
            </div>

            <!-- Beveiliging -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-3">Hoe beveiligen we je gegevens?</h2>
                <p class="text-gray-700 leading-relaxed">
                    We nemen passende technische en organisatorische maatregelen om je gegevens te beschermen:
                </p>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                    <li>Wachtwoorden worden versleuteld opgeslagen (hashing met bcrypt).</li>
                    <li>We gebruiken HTTPS om data tijdens het verzenden te versleutelen.</li>
                    <li>Onze applicatie draait op een beveiligde server met regelmatige updates.</li>
                    <li>Toegang tot de database is beperkt tot geautoriseerd personeel.</li>
                </ul>
            </div>

            <!-- Je rechten -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-3">Wat zijn je rechten?</h2>
                <p class="text-gray-700 leading-relaxed">
                    Volgens de AVG (Algemene Verordening Gegevensbescherming) heb je de volgende rechten:
                </p>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                    <li><strong>Recht op inzage:</strong> je kunt opvragen welke gegevens we van je hebben (inloggen geeft direct toegang tot al je data).</li>
                    <li><strong>Recht op correctie:</strong> je kunt je gegevens aanpassen via je profielpagina.</li>
                    <li><strong>Recht op verwijdering:</strong> je kunt je account (en daarmee al je data) verwijderen via je profielpagina.</li>
                    <li><strong>Recht op dataportabiliteit:</strong> je kunt een export van je gegevens opvragen (neem contact op via onderstaand e-mailadres).</li>
                    <li><strong>Recht van bezwaar:</strong> je kunt bezwaar maken tegen de verwerking van je gegevens (bijv. analytics weigeren).</li>
                </ul>
                <p class="text-gray-700 leading-relaxed mt-2">
                    Voor vragen over je privacy of om een van deze rechten uit te oefenen, kun je contact opnemen via het <a href="{{ route('home') }}#feedback" class="text-blue-600 hover:underline">feedbackformulier</a> op de homepage.
                </p>
            </div>

            <!-- Cookies -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-3">Cookies</h2>
                <p class="text-gray-700 leading-relaxed">
                    We gebruiken de volgende soorten cookies:
                </p>
                <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                    <li><strong>Functionele cookies:</strong> noodzakelijk voor login, sessies en beveiligingsfuncties (CSRF-beveiliging). Deze cookies zijn essentieel en vereisen geen toestemming.</li>
                    <li><strong>Analytics cookies (Google Analytics):</strong> worden gebruikt voor anonieme statistieken. We hebben <strong>Consent Mode v2</strong> ingesteld, waardoor advertenties en personalisatie zijn uitgeschakeld. Analytics draait alleen voor algemene websitestatistieken.</li>
                </ul>
                <p class="text-gray-700 leading-relaxed mt-2">
                    Je kunt analytics-cookies blokkeren via je browserinstellingen of een adblocker. Dit heeft geen invloed op de functionaliteit van de website.
                </p>
            </div>

            <!-- Wijzigingen -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-3">Wijzigingen in deze privacyverklaring</h2>
                <p class="text-gray-700 leading-relaxed">
                    We kunnen deze privacyverklaring van tijd tot tijd bijwerken. Belangrijke wijzigingen communiceren we via de website of per e-mail.
                    De datum van de laatste wijziging staat bovenaan deze pagina.
                </p>
            </div>

            <!-- Contact -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-3">Contact</h2>
                <p class="text-gray-700 leading-relaxed">
                    Voor vragen over privacy, gegevensverwerking of deze verklaring kun je contact opnemen via:
                </p>
                <ul class="list-none text-gray-700 space-y-1 mt-2">
                    <li><strong>Feedbackformulier:</strong> <a href="{{ route('home') }}#feedback" class="text-blue-600 hover:underline">Homepage</a></li>
                </ul>
            </div>

            <!-- Open Source -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-8">
                <h3 class="text-lg font-semibold text-blue-900 mb-2">Open Source & Transparantie</h3>
                <p class="text-blue-800 text-sm leading-relaxed">
                    {{ config('app.name') }} is volledig open source. Je kunt de broncode bekijken en controleren op
                    <a href="https://github.com/antwanvdm/jeugdvoetbalcoach" target="_blank" rel="noopener noreferrer" class="underline font-medium">GitHub</a>.
                    Zo kun je zelf zien hoe we met je data omgaan.
                </p>
            </div>
        </div>
    </section>
</x-app-layout>
