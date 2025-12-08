<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 top-row-actions">
        <h1 class="text-2xl font-semibold">Formatie {{ $formation->lineup_formation }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('formations.index') }}" class="px-3 py-2 bg-gray-200 rounded">Terug</a>
            @can('update', $formation)
                <a href="{{ route('formations.edit', $formation) }}" class="px-3 py-2 bg-yellow-600 text-white rounded">Bewerk</a>
            @endcan
            @can('delete', $formation)
                <form action="{{ route('formations.destroy', $formation) }}" method="POST" onsubmit="return confirm('Formatie verwijderen?')">
                    @csrf
                    @method('DELETE')
                    <button class="px-3 py-2 bg-red-600 text-white rounded cursor-pointer">Verwijder</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Info Block (Left) -->
        <div class="bg-white p-4 shadow rounded">
            <dl class="space-y-4">
                <div>
                    @if($formation->is_global)
                        <span class="inline-flex items-center text-xs px-2 py-1 bg-gray-100 rounded">Globale formatie</span>
                    @elseif($formation->user)
                        <span class="inline-flex items-center text-xs px-2 py-1 bg-yellow-100 rounded">Eigen formatie</span>
                    @endif
                </div>

                <div>
                    <dt class="text-sm text-gray-600 font-medium">Totaal spelers</dt>
                    <dd class="font-semibold text-lg">{{ $formation->total_players }}</dd>
                </div>

                <div>
                    <dt class="text-sm text-gray-600 font-medium">Opstelling</dt>
                    <dd class="font-semibold text-lg">{{ $formation->lineup_formation }}</dd>
                </div>
            </dl>
        </div>

        <!-- Visual Example (Right) -->
        <div class="sm:col-span-2 bg-white p-4 shadow rounded">
            <h2 class="text-lg font-semibold mb-4">Voorbeeld</h2>
            <div class="flex flex-col gap-4 sm:gap-6 bg-emerald-50 p-3 rounded line-up-player-rows">
                {{-- Aanvallers --}}
                <div class="flex flex-wrap justify-center gap-2 sm:gap-16 min-h-6">
                    @for($i = 0; $i < $attackers; $i++)
                        <span class="inline-block px-2 py-1 rounded text-white bg-green-600 text-sm">Aanvaller</span>
                    @endfor
                </div>
                {{-- Middenvelders --}}
                <div class="flex flex-wrap justify-center gap-2 sm:gap-16 min-h-6">
                    @for($i = 0; $i < $midfielders; $i++)
                        <span class="inline-block px-2 py-1 rounded text-white bg-green-600 text-sm">Middenvelder</span>
                    @endfor
                </div>
                {{-- Verdedigers --}}
                <div class="flex flex-wrap justify-center gap-2 sm:gap-16 min-h-6">
                    @for($i = 0; $i < $defenders; $i++)
                        <span class="inline-block px-2 py-1 rounded text-white bg-green-600 text-sm">Verdediger</span>
                    @endfor
                </div>
                {{-- Keeper(s) --}}
                <div class="flex flex-wrap justify-center gap-2 sm:gap-16 min-h-6">
                    <span class="inline-block px-2 py-1 rounded text-white bg-green-800 text-sm">Keeper</span>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
