<x-app-layout>
    <div class="mb-4 text-sm text-gray-600">
        Bedankt voor je registratie! Voor je begint, wil je je e-mailadres verifiëren door op de link te klikken die we je zojuist hebben gemaild? Als je de e-mail niet hebt ontvangen, sturen we je graag een nieuwe.
    </div>

    @if(session('pending_team_invite'))
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-800">
                <strong>Let op:</strong> Na verificatie van je e-mailadres kun je lid worden van het team en volledig aan de slag met {{ config('app.name') }}.
            </p>
        </div>
    @else
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-800">
                <strong>Let op:</strong> Na verificatie van je e-mailadres kun je volledig aan de slag met {{ config('app.name') }}.
            </p>
        </div>
    @endif

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            Er is een nieuwe verificatielink verzonden naar het e-mailadres dat je bij registratie hebt opgegeven.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Verificatie-e-mail opnieuw versturen
                </x-primary-button>
            </div>
        </form>

        <div class="flex gap-3">
            <a href="{{ route('dashboard') }}" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Naar dashboard
            </a>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Uitloggen
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
