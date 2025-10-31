<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Profielinformatie
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Wijzig je profielinformatie en e-mailadres.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Naam" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        Je e-mailadres is nog niet geverifieerd.

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Klik hier om de verificatie-e-mail opnieuw te verzenden.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            Een nieuwe verificatielink is verzonden naar je e-mailadres.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="team_name" value="Team Naam" />
            <x-text-input id="team_name" name="team_name" type="text" class="mt-1 block w-full" :value="old('team_name', $user->team_name)" required />
            <x-input-error class="mt-2" :messages="$errors->get('team_name')" />
        </div>

        <div>
            <x-input-label for="maps_location" value="Maps locatie (thuis)" />
            <x-text-input id="maps_location" name="maps_location" type="text" class="mt-1 block w-full" :value="old('maps_location', $user->maps_location)" required />
            <x-input-error class="mt-2" :messages="$errors->get('maps_location')" />
            <p class="mt-1 text-sm text-gray-600">Plak hier de Google Maps-link van jouw thuislocatie.</p>
        </div>

        <div>
            <x-input-label for="logo" value="Team Logo" />
            @if($user->logo)
                <div class="mt-2 mb-3">
                    <img src="{{ asset('storage/' . $user->logo) }}" alt="{{ $user->team_name }} Logo" class="h-20 rounded">
                    <p class="text-sm text-gray-600 mt-1">Huidig logo</p>
                </div>
            @endif
            <input id="logo" name="logo" type="file" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" accept="image/*" />
            <x-input-error class="mt-2" :messages="$errors->get('logo')" />
            <p class="mt-1 text-sm text-gray-600">Upload een nieuw logo om het huidige te vervangen (optioneel).</p>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Opslaan</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p data-auto-hide class="text-sm text-gray-600">Opgeslagen.</p>
            @endif
        </div>
    </form>
</section>
