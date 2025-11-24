<x-app-layout>
    <!-- Hero Section -->
    <section class="relative isolate overflow-hidden bg-home-hero bg-cover bg-center">
        {{--        <div class="absolute inset-0 -z-10 bg-cover bg-center" style="background-image:url('https://images.unsplash.com/photo-1517927033932-b3d18e61fb3a?q=80&w=1920&auto=format&fit=crop');"></div>--}}
        {{--        <div class="absolute inset-0 bg-gradient-to-br from-gray-900/80 via-gray-900/50 to-blue-900/60"></div>--}}
        <div class="mx-auto max-w-5xl px-6 py-20 text-center">
            <h1 class="text-4xl md:text-5xl font-bold tracking-tight text-white">Slim Teammanagement voor Jeugdvoetbal</h1>
            <p class="mt-6 text-lg text-blue-100 max-w-2xl mx-auto">Automatische, eerlijke line-ups, multi-team support en strategische rotatie. Focus op coaching – wij doen de planning.</p>
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-blue-600 px-8 py-3 text-white font-semibold shadow hover:bg-blue-500 transition">Registreren</a>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-white/10 backdrop-blur px-8 py-3 text-white font-medium ring-1 ring-inset ring-white/30 hover:bg-white/20 transition">Inloggen</a>
            </div>
            <div class="mt-10 flex flex-wrap justify-center gap-6 text-xs text-blue-100">
                <span class="inline-flex items-center gap-1"><svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m5 13 4 4L19 7"/></svg>Eerlijke rotatie</span>
                <span class="inline-flex items-center gap-1"><svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3v18m9-9H3"/></svg>Multi-team beheer</span>
                <span class="inline-flex items-center gap-1"><svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path
                            d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 8 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 3.6 15a1.65 1.65 0 0 0-1.51-1H2a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 3.6 8a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 8 3.6a1.65 1.65 0 0 0 1-1.51V2a2 2 0 0 1 4 0v.09c0 .67.39 1.28 1 1.51a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 8c.67 0 1.28.39 1.51 1H21a2 2 0 0 1 0 4h-.09c-.67 0-1.28.39-1.51 1Z"/></svg>Algoritmes & balans</span>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="mx-auto max-w-6xl px-6 py-16">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900 mb-6">Waarom deze tool?</h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($features as $f)
                <div class="group rounded-lg border bg-white p-5 shadow-sm hover:shadow-md transition">
                    <h3 class="font-semibold text-gray-900 mb-1 flex items-center gap-2">
                        <span class="inline-block size-2 rounded-full bg-blue-600 group-hover:scale-125 transition"></span>
                        {{ $f['title'] }}
                    </h3>
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $f['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <!-- How It Works -->
    <section class="mx-auto max-w-5xl px-6 pb-8">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900 mb-6">Hoe het werkt</h2>
        <ol class="space-y-4 grid-cols-1 md:grid-cols-2 grid">
            <li class="flex gap-4">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-white font-semibold">1</span>
                <div><h3 class="font-semibold">Account aanmaken</h3>
                    <p class="text-sm text-gray-600">Registreer en log in om je eerste team te starten.</p></div>
            </li>
            <li class="flex gap-4">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-white font-semibold">2</span>
                <div><h3 class="font-semibold">Team & Coaches</h3>
                    <p class="text-sm text-gray-600">Maak een team aan of join via invite code; stel rollen in.</p></div>
            </li>
            <li class="flex gap-4">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-white font-semibold">3</span>
                <div><h3 class="font-semibold">Spelers & Seizoen</h3>
                    <p class="text-sm text-gray-600">Voeg spelers toe, kies formatie en definieer seizoensblok.</p></div>
            </li>
            <li class="flex gap-4">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-white font-semibold">4</span>
                <div><h3 class="font-semibold">Wedstrijd & Line-up</h3>
                    <p class="text-sm text-gray-600">Plan een match en genereer een gebalanceerde opstelling.</p></div>
            </li>
        </ol>
    </section>

    <!-- Screenshot / Placeholder -->
    <section class="mx-auto max-w-6xl px-6 pb-16">
        <div class="rounded-xl border bg-gradient-to-br from-white to-blue-50 p-8 shadow-sm">
            <div class="flex flex-col lg:flex-row items-center gap-8">
                <div class="flex-1 space-y-4">
                    <h2 class="text-2xl font-bold text-gray-900">Voorbeeld Line-up</h2>
                    <p class="text-sm text-gray-600 leading-relaxed">Een gegenereerde opstelling toont per kwart de keeper, veldspelers en bank met optimale verdeling. Later verschijnt hier een echte screenshot (placeholder).</p>
                    <ul class="text-sm text-gray-700 list-disc list-inside space-y-1">
                        <li>4 kwarten overzichtelijk naast elkaar</li>
                        <li>Keeperrotatie zichtbaar met teller</li>
                        <li>Positie-iconen voor snelle herkenning</li>
                        <li>Printvriendelijk layout voor wedstrijddag</li>
                    </ul>
                    <a href="{{ route('register') }}" class="inline-flex mt-4 items-center gap-2 rounded-md bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-blue-500">Bekijk zelf</a>
                </div>
                <div class="flex-1 w-full aspect-video rounded-lg bg-gray-100 ring-1 ring-gray-200 flex items-center justify-center text-gray-500 text-sm">
                    Screenshot placeholder
                </div>
            </div>
        </div>
    </section>

    <!-- Call To Action -->
    <section class="mx-auto bg-blue-600 px-8 py-10 text-center shadow-md">
        <h2 class="text-2xl font-bold text-white mb-3">Start vandaag nog</h2>
        <p class="text-blue-100 mb-6 max-w-xl mx-auto">Maak gratis een account aan, nodig assistent-coaches uit en ervaar directe structuur in je wedstrijdvoorbereiding.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}" class="px-7 py-3 rounded-md bg-white text-blue-700 font-semibold shadow hover:bg-blue-50">Gratis Registreren</a>
            <a href="{{ route('login') }}" class="px-7 py-3 rounded-md bg-blue-500 text-white font-medium hover:bg-blue-400">Ik heb al een account</a>
        </div>
    </section>
</x-app-layout>
