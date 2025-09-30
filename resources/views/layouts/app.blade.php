<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'VVOR') }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 text-gray-900">
    <nav class="bg-white shadow mb-6">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center gap-4">
            <a href="/" class="font-semibold">VVOR</a>
            <a class="text-blue-600 hover:underline" href="{{ route('players.index') }}">Spelers</a>
            <a class="text-blue-600 hover:underline" href="{{ route('positions.index') }}">Posities</a>
            <a class="text-blue-600 hover:underline" href="{{ route('opponents.index') }}">Tegenstanders</a>
            <a class="text-blue-600 hover:underline" href="{{ route('football-matches.index') }}">Wedstrijden</a>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 mb-4 {{str_replace('.', '-', request()->route()->getName())}}">
        @if(session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
                <div class="font-semibold mb-2">Er zijn problemen met je invoer:</div>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>
</body>
</html>
