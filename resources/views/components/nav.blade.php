<nav class="bg-white shadow">
    <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between md:block">
        <div class="flex items-center justify-between gap-4">
            <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="font-semibold">
                @if(auth()->check() && session('current_team_id'))
                    @php
                        $currentTeam = \App\Models\Team::find(session('current_team_id'));
                    @endphp
                    @if($currentTeam && $currentTeam->logo)
                        <img src="{{ asset('storage/' . $currentTeam->logo) }}" alt="{{ $currentTeam->name }} Logo" class="h-12">
                    @else
                        <img src="{{Vite::asset('resources/images/logo.jpg')}}" alt="Logo" class="h-12">
                    @endif
                @else
                    <img src="{{Vite::asset('resources/images/logo.jpg')}}" alt="Logo" class="h-12">
                @endif
            </a>
            <div class="hidden md:flex items-center gap-3">
                @auth
                    {{-- Team Switcher Dropdown --}}
                    @if(auth()->user()->teams->count() > 0)
                        <div class="relative">
                            <button id="team-dropdown-btn" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-200 flex items-center gap-1">
                                @php
                                    $currentTeam = \App\Models\Team::find(session('current_team_id'));
                                @endphp
                                <span>{{ $currentTeam ? $currentTeam->name : 'Selecteer team' }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div id="team-dropdown-menu" class="hidden absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                                <div class="py-1">
                                    @foreach(auth()->user()->teams as $team)
                                        <form method="POST" action="{{ route('teams.switch', $team) }}">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ session('current_team_id') == $team->id ? 'bg-gray-50 font-semibold' : '' }}">
                                                {{ $team->name }}
                                                @if(session('current_team_id') == $team->id)
                                                    <span class="text-indigo-600">✓</span>
                                                @endif
                                            </button>
                                        </form>
                                    @endforeach
                                    <div class="border-t border-gray-100"></div>
                                    <a href="{{ route('teams.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Beheer teams
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <nav class="flex items-center gap-2">
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('formations.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-200 {{ request()->routeIs('formations.*') ? 'bg-gray-100' : '' }}">Formaties</a>
                            <a href="{{ route('admin.positions.index') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-200 {{ request()->routeIs('positions.*') ?
                            'bg-gray-100' : '' }}">Posities</a>
                            <a href="{{ route('admin.users.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-200 {{ request()->routeIs('admin.users.*') ? 'bg-gray-100' : '' }}">Gebruikers</a>
                        @else
                            <a href="{{ route('seasons.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-200 {{ request()->routeIs('seasons.*') ? 'bg-gray-100' : '' }}">Seizoenen</a>
                            <a href="{{ route('players.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-200 {{ request()->routeIs('players.*') ? 'bg-gray-100' : '' }}">Spelers</a>
                            <a href="{{ route('formations.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-200 {{ request()->routeIs('formations.*') ? 'bg-gray-100' : '' }}">Formaties</a>
                            <a href="{{ route('opponents.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-200 {{ request()->routeIs('opponents.*') ? 'bg-gray-100' : '' }}">Tegenstanders</a>
                            <a href="{{ route('football-matches.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-200 {{ request()->routeIs('football-matches.*') ? 'bg-gray-100' : '' }}">Wedstrijden</a>
                            <a href="{{ route('profile.edit') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-200 {{ request()->routeIs('profile.*') ? 'bg-gray-100' : '' }}">Profiel</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-200">Uitloggen</button>
                        </form>
                    </nav>
                @else
                    <div class="flex gap-2">
                        <a href="{{ route('login') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Inloggen</a>
                        <a href="{{ route('register') }}" class="px-3 py-2 rounded-md text-sm font-medium bg-blue-600 text-white hover:bg-blue-700">Registreren</a>
                    </div>
                @endauth
            </div>
        </div>

        <div class="md:hidden">
            <button id="nav-toggle" class="p-2 rounded border" aria-label="Open menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>
    <div id="nav-menu" class="hidden md:hidden px-4 pb-4">
        @auth
            @if(auth()->user()->isAdmin())
                <a class="block py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100 {{ request()->routeIs('formations.*') ? 'bg-gray-100' : '' }}" href="{{ route('formations.index') }}">Formaties</a>
                <a class="block py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.positions.*') ? 'bg-gray-100' : '' }}" href="{{ route('admin.positions.index') }}">Posities</a>
                <a class="block py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100 {{ request()->routeIs('admin.users.*') ? 'bg-gray-100' : '' }}" href="{{ route('admin.users.index') }}">Gebruikers</a>
            @else
                {{-- Team switcher voor mobile --}}
                <div class="py-2 px-3">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Team</div>
                    @foreach(auth()->user()->teams as $team)
                        <form method="POST" action="{{ route('teams.switch', $team) }}">
                            @csrf
                            <button type="submit" class="block w-full text-left py-1.5 px-2 rounded-md text-sm {{ session('current_team_id') == $team->id ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">
                                {{ $team->name }}
                                @if(session('current_team_id') == $team->id)
                                    <span class="text-indigo-600">✓</span>
                                @endif
                            </button>
                        </form>
                    @endforeach
                    <a class="block py-1.5 px-2 rounded-md text-sm text-gray-700 hover:bg-gray-100" href="{{ route('teams.index') }}">Beheer teams</a>
                </div>
                <div class="border-t border-gray-200 my-2"></div>
                <a class="block py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100 {{ request()->routeIs('seasons.*') ? 'bg-gray-100' : '' }}" href="{{ route('seasons.index') }}">Seizoenen</a>
                <a class="block py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100 {{ request()->routeIs('players.*') ? 'bg-gray-100' : '' }}" href="{{ route('players.index') }}">Spelers</a>
                <a class="block py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100 {{ request()->routeIs('formations.*') ? 'bg-gray-100' : '' }}" href="{{ route('formations.index') }}">Formaties</a>
                <a class="block py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100 {{ request()->routeIs('opponents.*') ? 'bg-gray-100' : '' }}" href="{{ route('opponents.index') }}">Tegenstanders</a>
                <a class="block py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100 {{ request()->routeIs('football-matches.*') ? 'bg-gray-100' : '' }}" href="{{ route('football-matches.index') }}">Wedstrijden</a>
                <a class="block py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100 {{ request()->routeIs('profile.*') ? 'bg-gray-100' : '' }}" href="{{ route('profile.edit') }}">Profiel</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full text-left py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100">Uitloggen</button>
            </form>
        @else
            <a class="block py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100" href="{{ route('login') }}">Inloggen</a>
            <a class="block py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100" href="{{ route('register') }}">Registreren</a>
        @endauth
    </div>
</nav>
