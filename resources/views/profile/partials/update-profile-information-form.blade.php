<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Profielinformatie
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
            Wijzig je profielinformatie en e-mailadres.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input id="updates_opt_out" name="updates_opt_out" type="checkbox" value="1" {{ old('updates_opt_out', $user->updates_opt_out) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
            </div>
            <div class="ml-3 text-sm">
                <label for="updates_opt_out" class="font-medium text-gray-700 dark:text-gray-300">Ik wil geen updates meer ontvangen over het platform</label>
                <p class="text-gray-500 dark:text-gray-400">Vink dit aan als je geen algemene e-mails over nieuwe functies of bugfixes wilt ontvangen.</p>
            </div>
        </div>

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
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-100">
                        Je e-mailadres is nog niet geverifieerd.

                        <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer">
                            Klik hier om de verificatie-e-mail opnieuw te verzenden.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            Een nieuwe verificatielink is verzonden naar je e-mailadres.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Opslaan</x-primary-button>
        </div>
    </form>
</section>
