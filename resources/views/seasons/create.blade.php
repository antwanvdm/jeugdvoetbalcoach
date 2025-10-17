<x-app-layout>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-semibold mb-4">Nieuw seizoen</h1>

        <div class="bg-white p-4 shadow rounded">
            <form action="{{ route('seasons.store') }}" method="POST">
                @csrf

                @include('seasons._form')

                <div class="mt-4">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded">Opslaan</button>
                    <a href="{{ route('seasons.index') }}" class="ml-2 text-gray-600">Annuleer</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
