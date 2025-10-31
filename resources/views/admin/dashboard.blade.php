<x-app-layout>
    <div class="max-w-5xl mx-auto">
        <h1 class="text-3xl font-semibold mb-4">{{ config('app.name') }}</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2">

                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('formations.index') }}" class="flex items-center gap-4 p-4 bg-white rounded shadow hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M6 7v10M18 7v10M9 7v10M15 7v10"/>
                        </svg>
                        <span class="text-lg font-medium">Formaties</span>
                    </a>

                    <a href="{{ route('admin.positions.index') }}" class="flex items-center gap-4 p-4 bg-white rounded shadow hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2v4c0 1.105 1.343 2 3 2s3-.895 3-2v-4c0-1.105-1.343-2-3-2z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v2"/>
                        </svg>
                        <span class="text-lg font-medium">Posities</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-4 p-4 bg-white rounded shadow hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1118.879 17.804" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-lg font-medium">Gebruikers</span>
                    </a>
                </div>
            </div>

            <div>
                <div class="bg-white p-4 shadow rounded">
                    <h2 class="text-lg font-semibold mb-3">Statistieken</h2>
                    <ul>
                        <li>...</li>
                        <li>...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
