<x-app-layout>
    <div class="max-w-5xl mx-auto">
        <h1 class="text-3xl font-semibold mb-4">{{ config('app.name') }}: {{$currentTeam->opponent->name}}</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2">

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
                        Team Manager is ontwikkeld voor voetbalverenigingen die hun teammanagement willen professionaliseren. Ideaal voor trainers van JO8 t/m JO12 waar spelers in 4 kwarten spelen en iedereen evenveel speel (en keep!)tijd
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
                                <li class="mb-2">
                                    <div class="text-sm text-gray-600">{{ $m->date->format('Y-m-d') }} - {{ $m->opponent?->name }}</div>
                                    <div class="font-medium">{{ $m->goals_scored ?? '-' }} - {{ $m->goals_conceded ?? '-' }} <span class="ml-2 text-sm text-gray-500">({{ $m->result }})</span></div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
