<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Team Manager') }}</title>
    <link rel="icon" type="image/png" href="{{asset('favicons/favicon-96x96.png')}}" sizes="96x96"/>
    <link rel="icon" type="image/svg+xml" href="{{asset('favicons/favicon.svg')}}"/>
    <link rel="shortcut icon" href="{{asset('favicons/favicon.ico')}}"/>
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('favicons/apple-touch-icon.png')}}"/>
    <meta name="apple-mobile-web-app-title" content="Team Manager"/>
    <link rel="manifest" href="{{asset('favicons/site.webmanifest')}}"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900">
<x-nav></x-nav>

<main class="@if(request()->route()->getName() !== 'home') max-w-5xl px-4 mb-4 mt-6 @endif mx-auto {{str_replace('.', '-', request()->route()->getName())}}">
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

    {{ $slot }}
</main>
</body>
</html>
