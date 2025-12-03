<x-app-layout>
    <div class="max-w-5xl mx-auto">
        <h1 class="text-3xl font-semibold mb-4">
            {{ config('app.name') }}: {{$currentTeam->opponent->name}}
            @php
                $currentTeamLabel = auth()->user()->teams()->where('teams.id', $currentTeam->id)->first()?->pivot->label;
            @endphp
            @if(!empty($currentTeamLabel))
                <span class="text-gray-500">({{ $currentTeamLabel }})</span>
            @endif
        </h1>

        @if(!auth()->user()->hasVerifiedEmail())
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 mb-6 rounded-r-lg">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">E-mailverificatie vereist</h3>
                        <p class="text-sm text-gray-700 mb-3">
                            Je moet eerst je e-mailadres verifiëren voordat je {{ config('app.name') }} kunt gebruiken.
                            @isset($onboardingComplete)
                                @if(!$onboardingComplete)
                                    Na verificatie kun je je onboarding afronden en volledig aan de slag.
                                @endif
                            @endisset
                        </p>
                        <a href="{{ route('verification.notice') }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            Verifieer je e-mailadres →
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2">

                @isset($onboardingComplete)
                    @if(!$onboardingComplete)
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg p-6 mb-6">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Voltooi je teamsetup</h3>
                                    <p class="text-sm text-gray-700 mb-4">Volg deze stappen om volledig aan de slag te gaan:</p>

                                    <div class="space-y-3">
                                        <!-- Step 1: Seizoen -->
                                        <div class="flex items-center gap-3">
                                            @if(($onboardingSteps['season'] ?? false))
                                                <div class="flex-shrink-0 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                                <span class="text-sm text-gray-600">Seizoen aangemaakt ✓</span>
                                            @else
                                                <div class="flex-shrink-0 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
                                                    <span class="text-white text-xs font-bold">1</span>
                                                </div>
                                                <a href="{{ route('seasons.create') }}" class="text-sm text-blue-700 font-medium hover:underline">Maak je eerste seizoen aan →</a>
                                            @endif
                                        </div>

                                        <!-- Step 2: Spelers -->
                                        <div class="flex items-center gap-3">
                                            @if(($onboardingSteps['players'] ?? false))
                                                <div class="flex-shrink-0 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                                <span class="text-sm text-gray-600">Spelers toegevoegd ✓</span>
                                            @elseif(!($onboardingSteps['season'] ?? false))
                                                <div class="flex-shrink-0 w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center">
                                                    <span class="text-gray-600 text-xs font-bold">2</span>
                                                </div>
                                                <span class="text-sm text-gray-500">Voeg spelers toe (eerst seizoen aanmaken)</span>
                                            @else
                                                <div class="flex-shrink-0 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
                                                    <span class="text-white text-xs font-bold">2</span>
                                                </div>
                                                <a href="{{ route('players.create') }}" class="text-sm text-blue-700 font-medium hover:underline">Voeg je eerste spelers toe →</a>
                                            @endif
                                        </div>

                                        <!-- Step 3: Wedstrijd -->
                                        <div class="flex items-center gap-3">
                                            @if(($onboardingSteps['match'] ?? false))
                                                <div class="flex-shrink-0 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                                <span class="text-sm text-gray-600">Eerste wedstrijd gepland ✓</span>
                                            @elseif(!($onboardingSteps['season'] ?? false) || !($onboardingSteps['players'] ?? false))
                                                <div class="flex-shrink-0 w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center">
                                                    <span class="text-gray-600 text-xs font-bold">3</span>
                                                </div>
                                                <span class="text-sm text-gray-500">Plan je eerste wedstrijd (eerst seizoen en spelers)</span>
                                            @else
                                                <div class="flex-shrink-0 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
                                                    <span class="text-white text-xs font-bold">3</span>
                                                </div>
                                                <a href="{{ route('football-matches.create', ['season_id' => $activeSeason?->id ?? '']) }}" class="text-sm text-blue-700 font-medium hover:underline">Plan je eerste wedstrijd →</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endisset

                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('seasons.index') }}" class="flex items-center gap-4 p-4 bg-white rounded shadow hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-lg font-medium">Seizoenen</span>
                    </a>

                    <a href="{{ route('players.index') }}" class="flex items-center gap-4 p-4 bg-white rounded shadow hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.636 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-lg font-medium">Spelers</span>
                    </a>

                    <a href="{{ route('formations.index') }}" class="flex items-center gap-4 p-4 bg-white rounded shadow hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M6 7v10M18 7v10M9 7v10M15 7v10"/>
                        </svg>
                        <span class="text-lg font-medium">Formaties</span>
                    </a>

                    <a href="{{ route('football-matches.index') }}" class="flex items-center gap-4 p-4 bg-white rounded shadow hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6h13M9 6L5 3v18"/>
                        </svg>
                        <span class="text-lg font-medium">Wedstrijden</span>
                    </a>

                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-4 p-4 bg-white rounded shadow hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1118.879 17.804" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-lg font-medium">Profiel</span>
                    </a>

                    <a href="{{ route('teams.index') }}" class="flex items-center gap-4 p-4 bg-white rounded shadow hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8a9 9 0 11-9-9"/>
                        </svg>
                        <span class="text-lg font-medium">Al mijn Teams</span>
                    </a>

                </div>

                <div class="bg-white p-4 shadow rounded mt-4">
                    <p>
                        {{config('app.name')}} is ontwikkeld voor voetbalverenigingen die hun teammanagement willen professionaliseren. Ideaal voor trainers van JO8 t/m JO12 waar spelers in 4 kwarten spelen en iedereen evenveel speel (en keep!)tijd
                        verdiend. De applicatie neemt het tijdrovende werk van het maken van line-ups uit handen en zorgt voor eerlijke rotatie en optimale teambalans.
                    </p>
                </div>
            </div>

            <div>
                @if($nextMatch)
                    <div class="bg-white p-4 shadow rounded flex flex-col gap-2">
                        <h2 class="text-lg font-semibold">Volgende wedstrijd</h2>
                        <time class="text-sm">{{ $nextMatch->date?->translatedFormat('j F Y H:i') }}</time>
                        <div class="mt-2 flex-1 flex justify-center items-center gap-4 @if(!$nextMatch->home) flex-row-reverse @endif">
                            <div class="flex-12 flex @if($nextMatch->home) justify-end @endif">
                                <img src="{{ asset('storage/' . $nextMatch->team->opponent->logo) }}" alt="{{ $nextMatch->team->name }} Logo" class="h-28">
                            </div>
                            <div class="flex-1">
                                -
                            </div>
                            <div class="flex-12 flex @if(!$nextMatch->home) justify-end @endif">
                                <img src="{{asset('storage/' . $nextMatch->opponent->logo)}}" alt="{{$nextMatch->opponent->name}} Logo" class="h-28">
                            </div>
                        </div>
                        <a href="{{ route('football-matches.show', $nextMatch) }}" class="mt-2 px-3 py-2 bg-indigo-600 text-white rounded text-center">Bekijk</a>
                    </div>
                @endif

                <div class="bg-white p-4 shadow rounded @if($nextMatch) mt-4 @endif">
                    <h2 class="text-lg font-semibold mb-3">Laatste resultaten</h2>
                    @if($recentMatches->isEmpty())
                        <div class="text-gray-500">Nog geen gespeelde wedstrijden.</div>
                    @else
                        <ul>
                            @foreach($recentMatches as $m)
                                <li class="pb-3 mb-3 border-b border-gray-200 last:border-b-0 last:mb-0">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-3">
                                            @if($m->opponent?->logo)
                                                <img src="{{ asset('storage/' . $m->opponent->logo) }}" alt="{{ $m->opponent?->name }}" class="h-8 w-8 object-contain">
                                            @else
                                                <div class="h-8 w-8 flex items-center justify-center bg-gray-100 rounded text-gray-400 text-xs">?</div>
                                            @endif
                                            <div>
                                                <div class="text-sm text-gray-600">{{ $m->date->translatedFormat('d-m-Y') }} • {{ $m->opponent?->name }} <span class="text-xs text-gray-500">({{ $m->home ? 'T' : 'U' }})</span></div>
                                                <div class="mt-1 font-bold result-{{$m->result}}">
                                                    @if($m->result !== 'O')
                                                        @if($m->home)
                                                            {{ $m->goals_scored }} - {{ $m->goals_conceded }}
                                                        @else
                                                            {{ $m->goals_conceded }} - {{ $m->goals_scored }}
                                                        @endif
                                                    @else
                                                        <span class="text-gray-500">-</span>
                                                    @endif
                                                    <span class="ml-2 text-xs text-gray-500">({{ $m->result }})</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
