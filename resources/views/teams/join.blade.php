<x-app-layout>
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow p-8">
            <h1 class="text-3xl font-bold mb-6">Team Uitnodiging</h1>

            <div class="mb-6">
                <p class="text-gray-700 mb-4">
                    Je bent uitgenodigd om deel te nemen aan:
                </p>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-semibold text-blue-900">{{ $team->opponent?->name }}</h2>
                            @if($team->opponent?->location)
                                <p class="text-sm text-blue-700 mt-1">{{ $team->opponent->location }}</p>
                            @endif
                        </div>
                        @if($team->opponent?->logo)
                            <img src="{{ asset('storage/' . $team->opponent->logo) }}" alt="{{ $team->opponent->name }}" class="h-16 w-16 object-contain">
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-yellow-800">
                    <strong>Let op:</strong> Door deel te nemen aan dit team krijg je toegang tot alle spelers, seizoenen, wedstrijden en andere data van dit team.
                    Je wordt toegevoegd als assistent-coach met volledige bewerkingsrechten.
                </p>
            </div>

            <form method="POST" action="{{ route('teams.join', $inviteCode) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Eigen label (optioneel)</label>
                    <input type="text" name="label" value="{{ old('label') }}" class="w-full border rounded p-2" placeholder="Bijv. JO8-1">
                    <p class="text-xs text-gray-600 mt-1">Handige eigen naam als je meerdere teams bij dezelfde club hebt.</p>
                    @error('label')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex gap-4">
                    <a href="{{ route('dashboard') }}" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-center font-medium">
                        Annuleren
                    </a>
                    <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        Bevestigen & Deelnemen
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
