<nav class="bg-white shadow mb-6">
    <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between md:block">
        <div class="flex items-center justify-between gap-4">
            <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="font-semibold">
                @if(auth()->check() && auth()->user()->logo)
                    <img src="{{ asset('storage/' . auth()->user()->logo) }}" alt="{{ auth()->user()->team_name ?? 'Team' }} Logo" class="h-12">
                @else
                    <img src="{{Vite::asset('resources/images/logo.jpg')}}" alt="Logo" class="h-12">
                @endif
            </a>
            <div class="hidden md:flex items-center gap-3">
                @auth
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
