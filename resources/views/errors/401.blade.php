<x-app-layout>
	<section class="mx-auto max-w-3xl px-6 py-16 text-center">
		<p class="text-8xl font-extrabold text-gray-300">401</p>
		<h1 class="mt-4 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Niet ingelogd') }}</h1>
		<p class="mt-2 text-gray-600 dark:text-gray-300">Je moet ingelogd zijn om deze pagina te zien.</p>
		<div class="mt-6 flex gap-3 justify-center">
			<a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-6 py-3 text-white font-semibold hover:bg-blue-500">Inloggen</a>
			<a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-md bg-gray-200 dark:bg-gray-700 px-6 py-3 text-gray-900 dark:text-gray-100 font-semibold hover:bg-gray-300 dark:bg-gray-600">Terug naar home</a>
		</div>
	</section>
  </x-app-layout>
