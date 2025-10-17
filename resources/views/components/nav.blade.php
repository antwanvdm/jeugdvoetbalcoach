<nav class="bg-white shadow mb-6">
    <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between md:block">
        <div class="flex items-center justify-between gap-4">
            <a href="/" class="font-semibold">
                <img src="{{config('app.vvor.logo')}}" alt="VVOR Logo" class="h-12">
            </a>
            <div class="hidden md:flex items-center gap-3">
                <nav class="flex items-center gap-2">
                    <a href="{{ route('seasons.index') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-200 {{ request()->routeIs('seasons.*') ? 'bg-gray-100' : '' }}">Seizoenen</a>
                    <a href="{{ route('players.index') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-200 {{ request()->routeIs('players.*') ? 'bg-gray-100' : '' }}">Spelers</a>
                    <a href="{{ route('formations.index') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-200 {{ request()->routeIs('formations.*') ? 'bg-gray-100' : '' }}">Formaties</a>
                    <a href="{{ route('positions.index') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-200 {{ request()->routeIs('positions.*') ? 'bg-gray-100' : '' }}">Posities</a>
                    <a href="{{ route('opponents.index') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-200 {{ request()->routeIs('opponents.*') ? 'bg-gray-100' : '' }}">Tegenstanders</a>
                    <a href="{{ route('football-matches.index') }}" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-200 {{ request()->routeIs('football-matches.*') ? 'bg-gray-100' : '' }}">Wedstrijden</a>
                </nav>
            </div>
        </div>

        <div class="md:hidden">
            <button id="nav-toggle" class="p-2 rounded border" aria-label="Open menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>
    <div id="nav-menu" class="hidden md:hidden px-4 pb-4">
        <a class="block py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100 {{ request()->routeIs('seasons.*') ? 'bg-gray-100' : '' }}" href="{{ route('seasons.index') }}">Seizoenen</a>
        <a class="block py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100 {{ request()->routeIs('players.*') ? 'bg-gray-100' : '' }}" href="{{ route('players.index') }}">Spelers</a>
        <a class="block py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100 {{ request()->routeIs('formations.*') ? 'bg-gray-100' : '' }}" href="{{ route('formations.index') }}">Formaties</a>
        <a class="block py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100 {{ request()->routeIs('positions.*') ? 'bg-gray-100' : '' }}" href="{{ route('positions.index') }}">Posities</a>
        <a class="block py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100 {{ request()->routeIs('opponents.*') ? 'bg-gray-100' : '' }}" href="{{ route('opponents.index') }}">Tegenstanders</a>
        <a class="block py-2 px-3 rounded-md text-gray-700 hover:bg-gray-100 {{ request()->routeIs('football-matches.*') ? 'bg-gray-100' : '' }}" href="{{ route('football-matches.index') }}">Wedstrijden</a>
    </div>
</nav>
