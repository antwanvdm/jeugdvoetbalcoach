<x-app-layout>
    <h1 class="text-2xl font-semibold mb-2">Registreren</h1>
    
    @if(session('team_invite'))
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-3">
                @if(session('team_invite')['team_logo'])
                    <img src="{{ asset('storage/' . session('team_invite')['team_logo']) }}" alt="Team" class="h-12 w-12 object-contain">
                @endif
                <div>
                    <p class="font-semibold text-blue-900">Je bent uitgenodigd voor {{ session('team_invite')['team_name'] }}</p>
                    <p class="text-sm text-blue-700">Maak een account aan om lid te worden van dit team.</p>
                </div>
            </div>
        </div>
    @else
        <p class="text-sm text-gray-600 mb-6">Maak een account aan voor je team. Teamnaam en logo zijn verplicht.</p>
    @endif

    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <!-- Naam -->
        <div>
            <x-input-label for="name" value="Naam" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- E-mailadres -->
        <div>
            <x-input-label for="email" value="E-mailadres" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        @unless(session('team_invite'))
            <!-- Club zoeken (opponent autocomplete) -->
            <div>
                <x-input-label for="club_search" value="Zoek club (autocomplete)" />
                <input id="club_search" data-opponent-autocomplete data-target-hidden="opponent_id" type="text" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" placeholder="Begin te typen..." autocomplete="off">
                <input type="hidden" name="opponent_id" id="opponent_id" value="{{ old('opponent_id') }}">
                <x-input-error :messages="$errors->get('opponent_id')" class="mt-2" />
            </div>
        @endunless

        <!-- Wachtwoord -->
        <div>
            <x-input-label for="password" value="Wachtwoord" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Bevestig wachtwoord -->
        <div>
            <x-input-label for="password_confirmation" value="Bevestig wachtwoord" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between pt-2">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                Al geregistreerd?
            </a>

            <x-primary-button class="ms-4">
                Registreren
            </x-primary-button>
        </div>
    </form>
</x-app-layout>
