<x-app-layout>
	<section class="mx-auto max-w-3xl px-6 py-16 text-center">
		<p class="text-8xl font-extrabold text-gray-300">419</p>
		<h1 class="mt-4 text-2xl font-semibold text-gray-900">{{ __('Pagina verlopen') }}</h1>
		<p class="mt-2 text-gray-600">Je sessie is verlopen. Vernieuw de pagina en probeer opnieuw.</p>
		<div class="mt-6">
			<a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-6 py-3 text-white font-semibold hover:bg-blue-500">Probeer opnieuw</a>
		</div>
	</section>
  </x-app-layout>
