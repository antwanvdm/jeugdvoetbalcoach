<x-app-layout>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 top-row-actions">
        <h1 class="text-2xl font-semibold">Tegenstander: {{ $opponent->name }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.opponents.index') }}" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 rounded">Terug</a>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.opponents.edit', $opponent) }}" class="px-3 py-2 bg-yellow-500 text-white rounded">Bewerk</a>
                <form action="{{ route('admin.opponents.destroy', $opponent) }}" method="POST" onsubmit="return confirm('Deze tegenstander verwijderen?')">
                    @csrf
                    @method('DELETE')
                    <button class="px-3 py-2 bg-red-600 text-white rounded">Verwijder</button>
                </form>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 p-4 shadow dark:shadow-gray-700 rounded max-w-3xl">
        <dl class="grid grid-cols-3 gap-2">
            <dt class="font-medium text-gray-600 dark:text-gray-300">Naam</dt>
            <dd class="col-span-2">{{ $opponent->name }}</dd>

            <dt class="font-medium text-gray-600 dark:text-gray-300">Locatie</dt>
            <dd class="col-span-2">
                <a href="{{ $opponent->locationMapsLink }}" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400 hover:underline">📍 Kaart</a>
            </dd>

            @if($opponent->address)
                <dt class="font-medium text-gray-600 dark:text-gray-300">Adres</dt>
                <dd class="col-span-2">
                    {!! str_replace(', ', '<br>', $opponent->address) !!}
                </dd>
            @endif

            @if($opponent->website)
                <dt class="font-medium text-gray-600 dark:text-gray-300">Website</dt>
                <dd class="col-span-2">
                    <a href="{{ $opponent->website }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">{{ $opponent->website }}</a>
                </dd>
            @endif

            <dt class="font-medium text-gray-600 dark:text-gray-300">Logo</dt>
            <dd class="col-span-2">
                @if($opponent->logo)
                    <img src="{{ asset('storage/' . $opponent->logo) }}" alt="{{ $opponent->name }} logo" class="h-28">
                @else
                    <span class="text-gray-500 dark:text-gray-400">-</span>
                @endif
            </dd>

            @if($opponent->kitUrl)
                <dt class="font-medium text-gray-600 dark:text-gray-300">Tenue</dt>
                <dd class="col-span-2">
                    <img src="{{ $opponent->kitUrl }}" alt="{{ $opponent->name }} kit" class="h-12">
                </dd>
            @endif
        </dl>
    </div>
</x-app-layout>
