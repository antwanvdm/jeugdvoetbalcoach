<x-app-layout>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Line-up voor wedstrijd tegen {{ $footballMatch->opponent->name ?? 'Onbekend' }}</h1>
        <a href="{{ route('football-matches.show', $footballMatch) }}" class="px-3 py-2 bg-gray-200 rounded">Terug</a>
    </div>

    <form action="{{ route('football-matches.lineup.update', $footballMatch) }}" method="POST" class="bg-white p-4 shadow rounded">
        @csrf

        <p class="text-sm text-gray-600 mb-4">
            Per kwart kun je spelers markeren als Aanwezig en vervolgens een positie geven. Laat de positie leeg (— Bank —) om de speler op de bank te zetten.
            Als je de speler niet als Aanwezig aanvinkt in een kwart, wordt hij als Afwezig beschouwd voor dat kwart.
        </p>

        <div class="mb-4">
            <button type="button" id="btn-all-present" class="px-3 py-2 bg-emerald-600 text-white rounded">Iedereen present (alle kwarten)</button>
        </div>

        @foreach(range(1,4) as $q)
            <div class="mb-6 border rounded">
                <div class="px-4 py-2 bg-gray-50 font-medium">Kwart {{ $q }}</div>
                <div class="p-4 overflow-x-auto">
                    <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                        <tr class="text-left text-gray-600">
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
                                    <select id="sel-q{{ $q }}-p{{ $player->id }}" class="border rounded px-2 py-1"
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

        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Sla line-up op</button>
            <a href="{{ route('football-matches.show', $footballMatch) }}" class="px-4 py-2 bg-gray-200 rounded">Annuleer</a>
        </div>
    </form>

    <script>
        document.addEventListener('change', function (e) {
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
        });

        // Iedereen present (alle kwarten)
        document.getElementById('btn-all-present')?.addEventListener('click', function () {
            const toggles = document.querySelectorAll('.present-toggle');
            toggles.forEach(function (cb) {
                if (!cb.checked) {
                    cb.checked = true;
                    // Trigger change handler to enable the select
                    cb.dispatchEvent(new Event('change', {bubbles: true}));
                } else {
                    // ensure select is enabled even if already checked
                    const selId = cb.getAttribute('data-target');
                    const sel = document.getElementById(selId);
                    if (sel) sel.disabled = false;
                }
            });
        });
    </script>
</x-app-layout>
