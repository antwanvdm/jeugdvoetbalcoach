<x-app-layout>
    @if($onboardingInProgress)
        <div class="mb-4 p-3 rounded-lg bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 flex items-center justify-between">
            <div class="text-sm text-blue-900 dark:text-blue-100">
                <strong>Teamsetup nog niet afgerond</strong> — Voltooi alle stappen om aan de slag te gaan.
            </div>
            <a href="{{ route('dashboard') }}" class="text-xs px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 dark:hover:bg-blue-600 whitespace-nowrap ml-2">Ga verder →</a>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row gap-4 sm:items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Spelers</h1>
        <div class="flex items-center gap-4">
            <form method="GET" action="{{ route('players.index') }}">
                <select name="season_id" onchange="this.form.submit()" class="border dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 py-2 pl-2 pr-8 rounded">
                    @foreach($seasons as $s)
                        <option value="{{ $s->id }}" {{ (int)($seasonId ?? 0) === $s->id ? 'selected' : '' }}>{{ $s->year }}/{{ $s->year + 1 }} - Fase {{ $s->part }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('players.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">Nieuwe speler(s)</a>
        </div>
    </div>

    <!-- Algemene rotatie informatie -->
    <div class="mb-4 p-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 mr-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="text-sm text-green-800 dark:text-green-200">
                <strong class="font-semibold">Automatische line-up generatie:</strong> Het algoritme zorgt ervoor dat alle spelers <strong>evenveel speeltijd</strong> krijgen over de 4 kwarten. Bankbeurten worden eerlijk verdeeld en de
                <strong>aangegeven sterkte</strong> van spelers wordt meegenomen om gebalanceerde teams per kwart te maken.
            </div>
        </div>
    </div>

    @php
        $keeperCount = $players->where('position_id', 1)->count();
        $wantsToKeepCount = $players->where('wants_to_keep', true)->where('position_id', '!=', 1)->count();
    @endphp

    @if($keeperCount > 0)
        <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm text-blue-800 dark:text-blue-300">
                    @if($keeperCount === 1)
                        <strong class="font-semibold">Let op:</strong> Je hebt 1 speler met favoriete positie "Keeper". Deze speler wordt <strong>alleen</strong> ingezet op de keeperspositie en komt <strong>nooit</strong> op de bank.
                        @if($wantsToKeepCount > 0)
                            (bij afwezigheid van vaste keepers worden andere spelers in roulatie gebruikt)
                        @endif
                    @else
                        <strong class="font-semibold">Let op:</strong> Je hebt {{ $keeperCount }} spelers met favoriete positie "Keeper". Deze spelers worden <strong>alleen</strong> ingezet op de keeperspositie, maar krijgen wel normale bankbeurten (net als andere spelers).
                        @if($wantsToKeepCount > 0)
                            (bij afwezigheid van vaste keepers worden andere spelers in roulatie gebruikt)
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @elseif($wantsToKeepCount > 0)
        <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm text-yellow-800 dark:text-yellow-300">
                    @if($wantsToKeepCount === 1)
                        <strong class="font-semibold">Let op:</strong> Je hebt 1 speler die wil keepen (roulatie). Deze speler wordt afgewisseld op de keeperspositie en speelt in de andere kwarten als <strong>veldspeler</strong>.
                    @else
                        <strong class="font-semibold">Let op:</strong> Je hebt {{ $wantsToKeepCount }} spelers die willen keepen (roulatie). Deze spelers wisselen elkaar af op de keeperspositie en spelen in de andere kwarten als <strong>veldspeler</strong>.
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm text-yellow-800 dark:text-yellow-300">
                    <strong class="font-semibold">Let op:</strong> Je hebt geen vaste keeper of spelers die willen keepen. Elke wedstrijd worden <strong>4 willekeurige spelers</strong> als keeper ingezet (1 per kwart).
                </div>
            </div>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-800 shadow dark:shadow-gray-700 rounded">
            <thead>
            <tr class="border-b">
                <th class="text-left p-3">Naam</th>
                <th class="text-left p-3">Favoriete positie</th>
                <th class="text-left p-3">Sterkere speler</th>
                <th class="text-left p-3 hidden sm:table-cell">Keeper status</th>
                <th class="text-right p-3"></th>
            </tr>
            </thead>
            <tbody>
            @forelse($players as $player)
                <tr class="border-b">
                    <td class="p-3">{{ $player->name }}</td>
                    <td class="p-3">{{ $player->position->name ?? '-' }}</td>
                    <td class="p-3">
                        @if($player->weight == 2)
                            <span class="inline-block px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs font-medium">Ja</span>
                        @else
                            <span class="inline-block px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded text-xs">Nee</span>
                        @endif
                    </td>
                    <td class="p-3 hidden sm:table-cell">
                        @if($player->position_id == 1)
                            <span class="inline-block px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded text-xs font-medium">Vast</span>
                        @elseif($player->wants_to_keep || ($keeperCount === 0 && $wantsToKeepCount === 0))
                            <span class="inline-block px-2 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded text-xs font-medium">Roulatie</span>
                        @else
                            <span class="text-gray-400">Nooit</span>
                        @endif
                    </td>
                    <td class="p-3 text-right">
                        <a class="text-blue-600 dark:text-blue-400 mr-2" href="{{ route('players.show', $player) }}">Bekijk</a>
                        <a class="text-yellow-600 mr-2 hidden md:inline" href="{{ route('players.edit', $player) }}">Bewerk</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="p-3 text-center text-gray-500 dark:text-gray-400">Voeg eerst spelers toe.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $players->links() }}</div>
</x-app-layout>
