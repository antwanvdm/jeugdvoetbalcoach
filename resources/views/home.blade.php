@push('head')
    <meta name="description" content="Automatische, eerlijke line-ups met slimme rotatie voor JO8 t/m JO12. Focus op coachen – wij doen de planning.">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="robots" content="index,follow">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ config('app.name') }} – Slim teammanagement voor jeugdcoaches">
    <meta property="og:description" content="Automatische, eerlijke line-ups met slimme rotatie. Perfect voor wedstrijden in 4 kwarten.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ Vite::asset('resources/images/vvor.jpg') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ config('app.name') }} – Slim teammanagement voor jeugdcoaches">
    <meta name="twitter:description" content="Automatische, eerlijke line-ups met slimme rotatie. Perfect voor 4 kwarten.">
    <meta name="twitter:image" content="{{ asset('favicons/apple-touch-icon.png') }}">
@endpush
<x-app-layout title="{{ config('app.name') }} – Slim teammanagement voor jeugdcoaches">
    <!-- Hero Section -->
    <section class="relative isolate overflow-hidden bg-home-hero bg-cover bg-center">
        <div class="mx-auto max-w-5xl px-6 py-20 text-center">
            <div class="mb-4">
                <span class="inline-block px-4 py-2 bg-blue-600/20 backdrop-blur-sm rounded-full text-sm font-medium text-blue-100 border border-blue-400/30">
                    Speciaal voor JO8 t/m JO12 trainers
                </span>
            </div>
            <h1 class="text-4xl md:text-6xl font-bold tracking-tight text-white mb-3">{{config('app.name')}}</h1>
            <p class="text-xl md:text-2xl text-blue-200 font-medium mb-6">Slim teammanagement voor jeugdcoaches</p>
            <p class="mt-4 text-lg text-blue-100 max-w-2xl mx-auto">Automatische, eerlijke line-ups met slimme rotatie. Perfect voor trainers die wedstrijden in 4 kwarten spelen. Focus op coaching – wij doen de planning.</p>
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-blue-600 px-8 py-3 text-white font-semibold shadow hover:bg-blue-500 transition">Registreren</a>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-white/10 backdrop-blur px-8 py-3 text-white font-medium ring-1 ring-inset ring-white/30 hover:bg-white/20 transition">Inloggen</a>
            </div>
        </div>
    </section>

    <!-- Infographic / USPs -->
    <section class="mx-auto max-w-6xl px-6 py-16">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900 mb-6">Wat kan het systeem voor jou doen?</h2>
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Spelers beheer -->
            <div class="bg-white rounded-xl border shadow-sm p-6 text-center hover:shadow-md transition">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Spelersbeheer</h3>
                <p class="text-sm text-gray-600">Voeg spelers toe met fysiek niveau en voorkeursposities</p>
            </div>

            <!-- Formatie keuze -->
            <div class="bg-white rounded-xl border shadow-sm p-6 text-center hover:shadow-md transition">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <circle cx="12" cy="8" r="1.5"/>
                        <circle cx="8" cy="12" r="1.5"/>
                        <circle cx="16" cy="12" r="1.5"/>
                        <circle cx="12" cy="16" r="1.5"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Formatie-keuze</h3>
                <p class="text-sm text-gray-600">Kies uit presets of maak eigen formaties per seizoen</p>
            </div>

            <!-- Automatische line-ups -->
            <div class="bg-white rounded-xl border shadow-sm p-6 text-center hover:shadow-md transition">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                        <rect x="9" y="3" width="6" height="4" rx="1"/>
                        <path d="M9 12h6"/>
                        <path d="M9 16h6"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Automatische Line-ups</h3>
                <p class="text-sm text-gray-600">Genereer eerlijke opstellingen met slimme rotatie algoritmes</p>
            </div>

            <!-- Delen & Printen -->
            <div class="bg-white rounded-xl border shadow-sm p-6 text-center hover:shadow-md transition">
                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="6 9 6 2 18 2 18 9"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <rect x="6" y="14" width="12" height="8"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2">Delen & Printen</h3>
                <p class="text-sm text-gray-600">Deel opstellingen met ouders en print voor de wedstrijd</p>
            </div>
        </div>
    </section>

    <!-- Screenshots / How It Works -->
    <section class="mx-auto max-w-6xl px-6 pb-16">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900 mb-6">Hoe het werkt</h2>
        <div class="space-y-16">
            <!-- Step 1: Team & Spelers -->
            <div class="grid lg:grid-cols-2 gap-8 items-center">
                <div class="order-2 lg:order-1">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-white font-bold text-lg">1</span>
                        <h3 class="text-xl font-semibold text-gray-900">Stel je team samen</h3>
                    </div>
                    <p class="text-gray-600 mb-4">Maak een team aan, nodig andere coaches uit en voeg je spelers toe. Geef aan wat hun fysieke niveau is en op welke posities ze het liefst spelen.</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="m5 13 4 4L19 7"/>
                            </svg>
                            Multi-team support voor meerdere teams
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="m5 13 4 4L19 7"/>
                            </svg>
                            Nodig assistent-coaches uit per team
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="m5 13 4 4L19 7"/>
                            </svg>
                            Spelers met fysiek niveau en voorkeursposities
                        </li>
                    </ul>
                </div>
                <div class="order-1 lg:order-2">
                    <div class="aspect-video rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-200 flex items-center justify-center shadow-lg">
                        <div class="text-center p-8">
                            <svg class="w-16 h-16 text-blue-400 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <path d="M3 9h18"/>
                                <path d="M9 21V9"/>
                            </svg>
                            <p class="text-sm text-blue-600 font-medium">Screenshot: Team & Spelersbeheer</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Seizoen & Formatie -->
            <div class="grid lg:grid-cols-2 gap-8 items-center">
                <div>
                    <div class="aspect-video rounded-xl bg-gradient-to-br from-green-50 to-green-100 border-2 border-green-200 flex items-center justify-center shadow-lg">
                        <div class="text-center p-8">
                            <svg class="w-16 h-16 text-green-400 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="12" cy="8" r="1.5"/>
                                <circle cx="8" cy="12" r="1.5"/>
                                <circle cx="16" cy="12" r="1.5"/>
                                <circle cx="12" cy="16" r="1.5"/>
                            </svg>
                            <p class="text-sm text-green-600 font-medium">Screenshot: Seizoen & Formatie</p>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-green-600 text-white font-bold text-lg">2</span>
                        <h3 class="text-xl font-semibold text-gray-900">Kies je formatie en seizoen</h3>
                    </div>
                    <p class="text-gray-600 mb-4">Definieer een seizoensblok en kies de formatie waarin je wilt spelen. Gebruik standaard presets of maak je eigen formaties aan.</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="m5 13 4 4L19 7"/>
                            </svg>
                            Meerdere seizoenen per team mogelijk
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="m5 13 4 4L19 7"/>
                            </svg>
                            Standaard formaties of eigen custom formaties
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="m5 13 4 4L19 7"/>
                            </svg>
                            Koppel wedstrijden aan het juiste seizoen
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Step 3: Genereer Line-up -->
            <div class="grid lg:grid-cols-2 gap-8 items-center">
                <div class="order-2 lg:order-1">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-600 text-white font-bold text-lg">3</span>
                        <h3 class="text-xl font-semibold text-gray-900">Genereer automatisch je line-up</h3>
                    </div>
                    <p class="text-gray-600 mb-4">Plan een wedstrijd en laat het systeem een eerlijke opstelling genereren. Het algoritme zorgt voor balans in speeltijd, posities en keeperrotatie.</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="m5 13 4 4L19 7"/>
                            </svg>
                            4 kwarten met eerlijke verdeling
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="m5 13 4 4L19 7"/>
                            </svg>
                            Automatische keeper- en bankrotatie
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="m5 13 4 4L19 7"/>
                            </svg>
                            Rekening houden met fysiek niveau
                        </li>
                    </ul>
                </div>
                <div class="order-1 lg:order-2">
                    <div class="aspect-video rounded-xl bg-gradient-to-br from-purple-50 to-purple-100 border-2 border-purple-200 flex items-center justify-center shadow-lg">
                        <div class="text-center p-8">
                            <svg class="w-16 h-16 text-purple-400 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path d="M9 5H7a2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
                                <rect x="9" y="3" width="6" height="4" rx="1"/>
                                <path d="M9 12h6"/>
                                <path d="M9 16h6"/>
                            </svg>
                            <p class="text-sm text-purple-600 font-medium">Screenshot: Gegenereerde Line-up</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Delen & Printen -->
            <div class="grid lg:grid-cols-2 gap-8 items-center">
                <div>
                    <div class="aspect-video rounded-xl bg-gradient-to-br from-orange-50 to-orange-100 border-2 border-orange-200 flex items-center justify-center shadow-lg">
                        <div class="text-center p-8">
                            <svg class="w-16 h-16 text-orange-400 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <polyline points="6 9 6 2 18 2 18 9"/>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                                <rect x="6" y="14" width="12" height="8"/>
                            </svg>
                            <p class="text-sm text-orange-600 font-medium">Screenshot: Delen & Printen</p>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-600 text-white font-bold text-lg">4</span>
                        <h3 class="text-xl font-semibold text-gray-900">Deel en print de opstelling</h3>
                    </div>
                    <p class="text-gray-600 mb-4">Deel de opstelling met ouders via een link of print het uit voor tijdens de wedstrijd. Alles overzichtelijk op één pagina.</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="m5 13 4 4L19 7"/>
                            </svg>
                            Deel via unieke link met ouders
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="m5 13 4 4L19 7"/>
                            </svg>
                            Print-vriendelijke layout
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="m5 13 4 4L19 7"/>
                            </svg>
                            Overzicht van alle 4 kwarten
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonial -->
    <section class="mx-auto max-w-4xl px-6 pb-16">
        <div class="bg-gradient-to-br from-blue-50 to-white rounded-xl border shadow-sm p-8 md:p-12">
            <div class="">
                <svg class="w-8 h-8 text-blue-600 mb-4" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                </svg>
                <p class="text-gray-700 text-lg leading-relaxed mb-6">
                    Als beginnend jeugdcoach van <a href="https://www.vvor.nl/" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 transition">VVOR <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0
                    1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/></svg></a> liep ik als snel tegen hetzelfde probleem aan: het bijhouden van wissels en opstellingen in een Excel werd erg onoverzichtelijk. Wie heeft er
                    al gespeeld? Wie moet er nog keepen? Hoe zorg ik dat iedereen evenveel speeltijd krijgt?
                </p>
                <p class="text-gray-700 text-lg leading-relaxed mb-6">
                    Daarom ben ik deze tool gaan bouwen. Een systeem dat automatisch eerlijke line-ups genereert, rekening houdt met fysiek niveau en posities, en alle administratie voor je bijhoudt. Nu kan ik me focussen op wat echt belangrijk
                    is: coachen!
                </p>
                <div class="border-t pt-4 flex gap-4 items-center">
                    <div class="w-20 h-20 bg-blue-600 rounded-full overflow-hidden flex items-center justify-center text-white font-bold text-2xl">
                        <img src="{{Vite::asset('resources/images/coach.webp')}}" alt="Coach Antwan aan het werk"/>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Antwan van der Mooren</p>
                        <p class="text-sm text-gray-600">Ontwikkelaar & Jeugdcoach</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contributing Section -->
    <section class="mx-auto max-w-6xl px-6 pb-16">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900 mb-6">Wil je bijdragen?</h2>
        <p class="text-gray-600 mb-8 max-w-3xl">Dit project is open source en we verwelkomen bijdragen van de community. Of je nu een bug vindt, een feature wilt toevoegen, of de servers draaiende wilt houden.</p>
        <div class="grid gap-6 md:grid-cols-2">
            <!-- GitHub Contributions -->
            <div class="bg-white rounded-xl border shadow-sm p-6 hover:shadow-md transition">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-12 h-12 text-gray-900" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 mb-2">Code bijdragen via GitHub</h3>
                        <p class="text-sm text-gray-600 mb-4">Vind je een bug of heb je een goed idee? Open een issue of stuur een pull request. Alle hulp is welkom!</p>
                        <a href="https://github.com/antwanvdm/jeugdvoetbalcoach" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                            Bekijk op GitHub
                        </a>
                    </div>
                </div>
            </div>

            <!-- GitHub Sponsors -->
            <div class="bg-white rounded-xl border shadow-sm p-6 hover:shadow-md transition">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-12 h-12 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 mb-2">Steun via GitHub Sponsors</h3>
                        <p class="text-sm text-gray-600 mb-4">Help mee om de servers draaiende te houden en nieuwe features te ontwikkelen. Elke bijdrage wordt enorm gewaardeerd!</p>
                        <a href="https://github.com/sponsors/antwanvdm" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-sm font-medium text-pink-600 hover:text-pink-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                            Word sponsor
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Feedback Form -->
    <section class="mx-auto max-w-6xl px-6 pb-16" id="feedback">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900 mb-6">Feedback of fout gevonden?</h2>
        <p class="text-gray-600 mb-8 max-w-3xl">Mis je clubgegevens of heb je andere feedback? Laat het ons weten!</p>
        <div class="bg-white rounded-xl border shadow-sm p-8">

            @if(session('feedback_success'))
                <div class="mb-6 rounded-lg bg-green-50 p-4 border border-green-200">
                    <div class="flex">
                        <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="ml-3 text-sm text-green-800">{{ session('feedback_success') }}</p>
                    </div>
                </div>
            @endif

            <form action="{{ route('home.feedback') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Naam</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 @enderror">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-300 @enderror">
                        @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Onderwerp</label>
                    <select name="subject" id="subject" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('subject') border-red-300 @enderror">
                        <option value="">Selecteer een onderwerp...</option>
                        <option value="Clubgegevens onjuist" {{ old('subject') == 'Clubgegevens onjuist' ? 'selected' : '' }}>Clubgegevens onjuist</option>
                        <option value="Bug gevonden" {{ old('subject') == 'Bug gevonden' ? 'selected' : '' }}>Bug gevonden</option>
                        <option value="Feature verzoek" {{ old('subject') == 'Feature verzoek' ? 'selected' : '' }}>Feature verzoek</option>
                        <option value="Algemene feedback" {{ old('subject') == 'Algemene feedback' ? 'selected' : '' }}>Algemene feedback</option>
                        <option value="Anders" {{ old('subject') == 'Anders' ? 'selected' : '' }}>Anders</option>
                    </select>
                    @error('subject')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Bericht</label>
                    <textarea name="message" id="message" rows="5" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('message') border-red-300 @enderror">{{ old('message') }}</textarea>
                    @error('message')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-3 text-white font-semibold shadow hover:bg-blue-500 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Verstuur feedback
                    </button>
                </div>
            </form>
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
