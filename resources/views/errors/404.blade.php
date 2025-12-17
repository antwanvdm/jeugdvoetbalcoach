<x-app-layout>
	<section class="mx-auto max-w-3xl px-6 py-16 text-center">
		<p class="text-8xl font-extrabold text-gray-300">404</p>
		<h1 class="mt-4 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Pagina niet gevonden') }}</h1>
		<p class="mt-2 text-gray-600 dark:text-gray-300">De pagina die je zoekt bestaat niet of is verplaatst.</p>
		<div class="mt-6">
			<a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-6 py-3 text-white font-semibold hover:bg-blue-500">
				Terug naar home
			</a>
		</div>
	</section>
  </x-app-layout>
