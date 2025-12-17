@props(['title'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ request()->cookie('theme') === 'dark' ? 'dark' : '' }}">
<head>
    <script>
        const theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        if (theme === 'dark') document.documentElement.classList.add('dark');
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{asset('favicons/favicon-96x96.png')}}" sizes="96x96"/>
    <link rel="icon" type="image/svg+xml" href="{{asset('favicons/favicon.svg')}}"/>
    <link rel="shortcut icon" href="{{asset('favicons/favicon.ico')}}"/>
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('favicons/apple-touch-icon.png')}}"/>
    <meta name="apple-mobile-web-app-title" content="Coach"/>
    <link rel="manifest" href="{{asset('favicons/site.webmanifest')}}"/>
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 flex flex-col min-h-screen">
<x-nav></x-nav>

<div class="grow">
    <main class="@if(request()->route()?->getName() !== 'home') max-w-5xl px-4 mb-8 mt-6 @else w-full @endif mx-auto {{str_replace('.', '-', request()->route()?->getName())}}">
        @if(session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100">
                {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="mb-4 p-3 rounded bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100">
                {{ session('info') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3 rounded bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-100">
                {{ session('error') }}
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="mb-4 p-3 rounded bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-100">
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
</div>

<x-footer></x-footer>
@include('partials.cookie-consent')
</body>
</html>
