<x-app-layout>
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-700 p-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-6">Team Uitnodiging</h1>

            <div class="mb-6">
                <p class="text-gray-700 dark:text-gray-200 mb-4">
                    Je bent uitgenodigd om deel te nemen aan:
                </p>

                <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-semibold text-blue-900 dark:text-blue-100">{{ $team->opponent?->name }}</h2>
                            @if($team->opponent?->location)
                                <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">{{ $team->opponent->location }}</p>
                            @endif
                        </div>
                        @if($team->opponent?->logo)
                            <img src="{{ asset('storage/' . $team->opponent->logo) }}" alt="{{ $team->opponent->name }}" class="h-16 w-16 object-contain">
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 mb-6">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    <strong>Let op:</strong> Door deel te nemen aan dit team krijg je toegang tot alle spelers, seizoenen, wedstrijden en andere data van dit team.
                </p>
            </div>

            <form method="POST" action="{{ route('teams.join', $inviteCode) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">Eigen label (optioneel)</label>
                    <input type="text" name="label" value="{{ old('label') }}" class="w-full border dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded p-2" placeholder="Bijv. JO8-1">
                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">Handige eigen naam als je meerdere teams bij dezelfde club hebt.</p>
                    @error('label')
                        <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 font-medium cursor-pointer">
                        Bevestigen & Deelnemen
                    </button>
                </div>
            </form>
            <form method="POST" action="{{ route('teams.join.decline', $inviteCode) }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium cursor-pointer">
                    Weigeren
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
