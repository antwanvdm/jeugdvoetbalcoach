<nav class="bg-white shadow mb-6">
    <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between md:block">
        <div class="flex items-center justify-between gap-4">
            <a href="/" class="font-semibold">
                <img src="{{config('app.vvor.logo')}}" alt="VVOR Logo" class="h-12">
            </a>
            <div class="hidden md:flex items-center gap-6">
                <a class="text-blue-800 hover:underline" href="{{ route('seasons.index') }}">Seizoenen</a>
                <a class="text-blue-800 hover:underline" href="{{ route('players.index') }}">Spelers</a>
                <a class="text-blue-800 hover:underline" href="{{ route('formations.index') }}">Formaties</a>
                <a class="text-blue-800 hover:underline" href="{{ route('positions.index') }}">Posities</a>
                <a class="text-blue-800 hover:underline" href="{{ route('opponents.index') }}">Tegenstanders</a>
                <a class="text-blue-800 hover:underline" href="{{ route('football-matches.index') }}">Wedstrijden</a>
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
        <a class="block py-2 text-blue-800 hover:underline" href="{{ route('seasons.index') }}">Seizoenen</a>
        <a class="block py-2 text-blue-800 hover:underline" href="{{ route('players.index') }}">Spelers</a>
        <a class="block py-2 text-blue-800 hover:underline" href="{{ route('formations.index') }}">Formaties</a>
        <a class="block py-2 text-blue-800 hover:underline" href="{{ route('positions.index') }}">Posities</a>
        <a class="block py-2 text-blue-800 hover:underline" href="{{ route('opponents.index') }}">Tegenstanders</a>
        <a class="block py-2 text-blue-800 hover:underline" href="{{ route('football-matches.index') }}">Wedstrijden</a>
    </div>
</nav>
