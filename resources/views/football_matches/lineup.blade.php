<x-app-layout>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Line-up voor wedstrijd tegen {{ $footballMatch->opponent->name ?? 'Onbekend' }}</h1>
        <a href="{{ route('football-matches.show', $footballMatch) }}" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 rounded">Terug</a>
    </div>

    <form action="{{ route('football-matches.lineup.update', $footballMatch) }}" method="POST" class="bg-white dark:bg-gray-800 p-4 shadow dark:shadow-gray-700 rounded">
        @csrf

        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
            Per kwart kun je spelers markeren als Aanwezig en vervolgens een positie geven. Laat de positie leeg (— Bank —) om de speler op de bank te zetten.
            Als je de speler niet als Aanwezig aanvinkt in een kwart, wordt hij als Afwezig beschouwd voor dat kwart.
        </p>

        <!-- Master Attendance Section -->
        <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Aanwezigheid deze wedstrijd
            </h3>
            <p class="text-xs text-gray-600 dark:text-gray-300 mb-3">Vink spelers aan die de hele wedstrijd aanwezig zijn (alle 4 kwarten). Je kunt per kwart nog wijzigingen maken.</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                @foreach($players as $player)
                    @php
                        // Check if player is present in all quarters
                        $presentInAll = true;
                        foreach(range(1,4) as $q) {
                            if (!array_key_exists($player->id, $existing[$q] ?? [])) {
                                $presentInAll = false;
                                break;
                            }
                        }
                    @endphp
                    <label class="flex items-center gap-2 text-sm bg-white dark:bg-gray-800 rounded px-3 py-2 hover:bg-blue-100 dark:bg-blue-900 cursor-pointer border border-gray-200 dark:border-gray-700">
                        <input type="checkbox" class="master-attendance-toggle" data-player-id="{{ $player->id }}" {{ $presentInAll ? 'checked' : '' }}>
                        <span class="truncate">{{ $player->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="mb-4">
            <button type="button" id="btn-all-present" class="px-3 py-2 bg-emerald-600 text-white rounded">Iedereen present (alle kwarten)</button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach(range(1,4) as $q)
            <div class="border rounded">
                <div class="px-4 py-2 bg-gray-50 dark:bg-gray-900 font-medium">Kwart {{ $q }}</div>
                <div class="p-4 overflow-x-auto">
                    <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                        <tr class="text-left text-gray-600 dark:text-gray-300">
                            <th class="py-2 pr-4">Speler</th>
                            <th class="py-2 pr-4">Aanwezig</th>
                            <th class="py-2 pr-4">Positie</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($players as $player)
                            @php
                                $present = array_key_exists($player->id, $existing[$q] ?? []);
                                $posId = $existing[$q][$player->id] ?? '';
                            @endphp
                            <tr class="border-t">
                                <td class="py-2 pr-4">{{ $player->name }}</td>
                                <td class="py-2 pr-4">
                                    <input type="checkbox" class="present-toggle" data-target="sel-q{{ $q }}-p{{ $player->id }}" {{ $present ? 'checked' : '' }}>
                                </td>
                                <td class="py-2 pr-4">
                                    <select id="sel-q{{ $q }}-p{{ $player->id }}" class="border rounded px-2 py-1 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                                            name="assignments[{{ $q }}][{{ $player->id }}]"
                                        {{ $present ? '' : 'disabled' }}>
                                        <option value="">— Bank —</option>
                                        @foreach($positions as $id => $name)
                                            <option value="{{ $id }}" @selected((string)$posId === (string)$id)>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        @endforeach
        </div>

        <div class="flex gap-2 mt-6">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Sla line-up op</button>
            <a href="{{ route('football-matches.show', $footballMatch) }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded">Annuleer</a>
        </div>
    </form>

    <script>
        document.addEventListener('change', function (e) {
            // Handle quarter-specific present toggles
            if (e.target.classList.contains('present-toggle')) {
                const selId = e.target.getAttribute('data-target');
                const sel = document.getElementById(selId);
                if (!sel) return;
                sel.disabled = !e.target.checked;
                // Als uitgevinkt, maak selectie leeg om onbedoeld "Bank" posten te voorkomen
                if (sel.disabled) {
                    sel.value = '';
                }
            }

            // Handle master attendance toggles
            if (e.target.classList.contains('master-attendance-toggle')) {
                const playerId = e.target.getAttribute('data-player-id');
                const isChecked = e.target.checked;

                // Update all 4 quarters for this player
                for (let q = 1; q <= 4; q++) {
                    const quarterCheckbox = document.querySelector(`.present-toggle[data-target="sel-q${q}-p${playerId}"]`);
                    if (quarterCheckbox) {
                        quarterCheckbox.checked = isChecked;
                        // Trigger change to enable/disable select
                        quarterCheckbox.dispatchEvent(new Event('change', {bubbles: true}));
                    }
                }
            }
        });

        // Iedereen present (alle kwarten)
        document.getElementById('btn-all-present')?.addEventListener('click', function () {
            // Check all master toggles
            const masterToggles = document.querySelectorAll('.master-attendance-toggle');
            masterToggles.forEach(function (cb) {
                if (!cb.checked) {
                    cb.checked = true;
                    cb.dispatchEvent(new Event('change', {bubbles: true}));
                }
            });
        });
    </script>
</x-app-layout>
