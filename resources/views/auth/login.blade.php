<x-app-layout>
    <h1 class="text-2xl font-semibold mb-2">Inloggen</h1>
    <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">Log in om je team te beheren.</p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- E-mailadres -->
        <div>
            <x-input-label for="email" value="E-mailadres" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Wachtwoord -->
        <div class="mt-4">
            <x-input-label for="password" value="Wachtwoord" />

            <x-text-input id="password" class="block mt-1 w-full dark:scheme-dark"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div>
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-300">Onthoud mij</span>
            </label>
        </div>

        <div class="flex items-center justify-between pt-2">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    Wachtwoord vergeten?
                </a>
            @endif

            <x-primary-button class="ms-3">
                Inloggen
            </x-primary-button>
        </div>

        <div class="text-sm text-gray-600 dark:text-gray-300 mt-4">
            Nog geen account?
            <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 underline">Registreer nu</a>
        </div>
    </form>
</x-app-layout>
