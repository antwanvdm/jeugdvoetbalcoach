<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Account verwijderen
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Zodra je account is verwijderd, worden al je gegevens permanent verwijderd.
        </p>

        @if($teams->isNotEmpty())
            <div class="mt-4 rounded-md bg-yellow-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Let op: Impact op je teams</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p class="mb-2">Je bent lid van de volgende teams:</p>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($teams as $team)
                                    @php
                                        $teamLabel = $team->pivot->label;
                                        $teamName = $team->opponent?->name . ($teamLabel ? " ({$teamLabel})" : '');
                                    @endphp
                                    <li>
                                        <strong>{{ $teamName }}</strong>
                                        @if($team->users_count === 1)
                                            <span class="text-red-700 font-semibold">
                                                — wordt volledig verwijderd inclusief alle spelers, wedstrijden, seizoenen en formaties (je bent het enige lid)
                                            </span>
                                        @else
                                            <span class="text-green-700">
                                                — blijft bestaan ({{ $team->users_count - 1 }} {{ $team->users_count - 1 === 1 ? 'andere coach' : 'andere coaches' }})
                                            </span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </header>

    <x-danger-button onclick="openModal('confirm-user-deletion')">
        Account verwijderen
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                Weet je zeker dat je je account wilt verwijderen?
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Deze actie kan niet ongedaan worden gemaakt. Alle gegevens worden permanent verwijderd.
            </p>

            @if($teams->isNotEmpty())
                <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
                    <p class="text-sm font-medium text-red-800">Wat wordt er verwijderd:</p>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                        @foreach($teams as $team)
                            @if($team->users_count === 1)
                                @php
                                    $teamLabel = $team->pivot->label;
                                    $teamName = $team->opponent?->name . ($teamLabel ? " ({$teamLabel})" : '');
                                @endphp
                                <li>Team <strong>{{ $teamName }}</strong> inclusief alle spelers, wedstrijden, seizoenen en formaties</li>
                            @endif
                        @endforeach
                        @if($teams->where('users_count', '>', 1)->count() > 0)
                            <li>Je toegang tot:
                                @foreach($teams->where('users_count', '>', 1) as $team)
                                    @php
                                        $teamLabel = $team->pivot->label;
                                        $teamName = $team->opponent?->name . ($teamLabel ? " ({$teamLabel})" : '');
                                    @endphp
                                    {{ $loop->first ? '' : ', ' }}{{ $teamName }}
                                @endforeach
                            </li>
                        @endif
                    </ul>
                </div>
            @endif

            <div class="mt-6">
                <x-input-label for="password" value="Wachtwoord" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Wachtwoord"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button type="button" onclick="closeModal('confirm-user-deletion')">
                    Annuleren
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    Account verwijderen
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
