<nav class="bg-white dark:bg-gray-800 shadow dark:shadow-gray-700">
    <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between md:block">
        <div class="flex items-center justify-between gap-4">
            <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="font-semibold">
                @if(auth()->check() && session('current_team_id') && request()->route()->getName() !== 'home')
                    @php
                        $currentTeam = \App\Models\Team::find(session('current_team_id'));
                    @endphp
                    @if($currentTeam && $currentTeam->opponent->logo)
                        <img src="{{ asset('storage/' . $currentTeam->opponent->logo) }}" alt="{{ $currentTeam->opponent->name }} Logo" class="h-12">
                    @else
                        <img src="{{Vite::asset('resources/images/logo-small.webp')}}" alt="{{config('app.name')}} Logo" class="h-12">
                    @endif
                @else
                    <img src="{{Vite::asset('resources/images/logo-small.webp')}}" alt="{{config('app.name')}} Logo" class="h-12">
                @endif
            </a>
            <div class="hidden md:flex items-center gap-3">
                @auth
                    {{-- Team Switcher Dropdown --}}
                    @if(auth()->user()->teams->count() > 0)
                        <div class="relative">
                            <button id="team-dropdown-btn" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 flex items-center gap-1 cursor-pointer">
                                @php
                                    $currentTeam = \App\Models\Team::find(session('current_team_id'));
                                    $currentTeamLabel = $currentTeam ? auth()->user()->teams()->where('teams.id', $currentTeam->id)->first()?->pivot->label : null;
                                @endphp
                                <span>
                                    {{ $currentTeam ? $currentTeam->opponent->name : 'Selecteer team' }}
                                    @if(!empty($currentTeamLabel))
                                        <span class="text-gray-500">({{ $currentTeamLabel }})</span>
                                    @endif
                                </span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="team-dropdown-menu" class="hidden absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black dark:ring-gray-700 ring-opacity-5 z-50">
                                <div class="py-1">
                                    @foreach(auth()->user()->teams as $team)
                                        <form method="POST" action="{{ route('teams.switch', $team) }}">
                                            @csrf
                                            <button type="submit" class="block cursor-pointer w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ session('current_team_id') == $team->id ? 'bg-gray-50
                                            dark:bg-gray-700 font-semibold' : '' }}">
                                                {{ $team->opponent->name }}
                                                @if(!empty($team->pivot->label))
                                                    <span class="text-gray-500">{{ $team->pivot->label }}</span>
                                                @endif
                                                @if(session('current_team_id') == $team->id)
                                                    <span class="text-indigo-600 dark:text-indigo-400 font-bold">✓</span>
                                                @endif
                                            </button>
                                        </form>
                                    @endforeach
                                    <div class="border-t border-gray-100 dark:border-gray-700"></div>
                                    <a href="{{ route('teams.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        Beheer teams
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <nav class="flex items-center gap-2">
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('formations.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 {{ request()->routeIs('formations.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">Formaties</a>
                            <a href="{{ route('admin.positions.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 {{ request()->routeIs('admin.positions.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">Posities</a>
                            <a href="{{ route('admin.opponents.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 {{ request()->routeIs('admin.opponents.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">Clubs</a>
                            <a href="{{ route('admin.users.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 {{ request()->routeIs('admin.users.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">Gebruikers</a>
                        @else
                            <a href="{{ route('seasons.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 {{ request()->routeIs('seasons.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">Seizoenen</a>
                            <a href="{{ route('players.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 {{ request()->routeIs('players.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">Spelers</a>
                            <a href="{{ route('formations.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 {{ request()->routeIs('formations.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">Formaties</a>
                            <a href="{{ route('football-matches.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 {{ request()->routeIs('football-matches.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">Wedstrijden</a>
                            <a href="{{ route('profile.edit') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 {{ request()->routeIs('profile.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">Profiel</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 cursor-pointer">Uitloggen</button>
                        </form>
                    </nav>
                @else
                    <div class="flex gap-2">
                        <a href="{{ route('login') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Inloggen</a>
                        <a href="{{ route('register') }}" class="px-3 py-2 rounded-md text-sm font-medium bg-blue-600 dark:bg-blue-500 text-white hover:bg-blue-700 dark:hover:bg-blue-600">Registreren</a>
                    </div>
                @endauth
            </div>
        </div>

        <div class="md:hidden">
            <button id="nav-toggle" class="p-2 rounded border dark:border-gray-600" aria-label="Open menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>
    <div id="nav-menu" class="hidden md:hidden px-4 pb-4">
        @auth
            @if(auth()->user()->isAdmin())
                <a class="block py-2 px-3 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('formations.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}" href="{{ route('formations.index') }}">Formaties</a>
                <a class="block py-2 px-3 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('admin.positions.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}" href="{{ route('admin.positions.index') }}">Posities</a>
                <a class="block py-2 px-3 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('admin.opponents.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}" href="{{ route('admin.opponents.index') }}">Clubs</a>
                <a class="block py-2 px-3 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('admin.users.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}" href="{{ route('admin.users.index') }}">Gebruikers</a>
            @else
                {{-- Team switcher voor mobile --}}
                <div class="py-2 px-3">
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Team</div>
                    @foreach(auth()->user()->teams as $team)
                        <form method="POST" action="{{ route('teams.switch', $team) }}">
                            @csrf
                            <button type="submit" class="block cursor-pointer w-full text-left py-1.5 px-2 rounded-md text-sm {{ session('current_team_id') == $team->id ? 'bg-indigo-50 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-200 font-semibold' :
                            'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                {{ $team->opponent->name }}
                                @if(!empty($team->pivot->label))
                                    <span class="text-gray-500 dark:text-gray-400">({{ $team->pivot->label }})</span>
                                @endif
                                @if(session('current_team_id') == $team->id)
                                    <span class="text-indigo-600 dark:text-indigo-400">✓</span>
                                @endif
                            </button>
                        </form>
                    @endforeach
                    <a class="block py-1.5 px-2 rounded-md text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" href="{{ route('teams.index') }}">Beheer teams</a>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
                <a class="block py-2 px-3 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('seasons.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}" href="{{ route('seasons.index') }}">Seizoenen</a>
                <a class="block py-2 px-3 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('players.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}" href="{{ route('players.index') }}">Spelers</a>
                <a class="block py-2 px-3 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('formations.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}" href="{{ route('formations.index') }}">Formaties</a>
                <a class="block py-2 px-3 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('football-matches.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}" href="{{ route('football-matches.index') }}">Wedstrijden</a>
                <a class="block py-2 px-3 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('profile.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}" href="{{ route('profile.edit') }}">Profiel</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full text-left py-2 px-3 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Uitloggen</button>
            </form>
        @else
            <a class="block py-2 px-3 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" href="{{ route('login') }}">Inloggen</a>
            <a class="block py-2 px-3 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" href="{{ route('register') }}">Registreren</a>
        @endauth
    </div>
</nav>
