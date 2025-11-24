<footer class="bg-gray-900 text-gray-300 mt-auto">
    <div class="mx-auto max-w-7xl px-6 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- About -->
            <div>
                <h3 class="text-white font-semibold mb-4">{{ config('app.name') }}</h3>
                <p class="text-sm text-gray-400 leading-relaxed">
                    Slim teammanagement voor jeugdvoetbal. Automatische line-ups met eerlijke rotatie en multi-team support.
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-white font-semibold mb-4">Navigatie</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition">Home</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white transition">Registreren</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white transition">Inloggen</a></li>
                </ul>
            </div>

            <!-- Resources -->
            <div>
                <h3 class="text-white font-semibold mb-4">Informatie</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="#features" class="hover:text-white transition">Functionaliteiten</a></li>
                    <li><a href="#how-it-works" class="hover:text-white transition">Hoe het werkt</a></li>
                </ul>
            </div>

            <!-- Contact & Credits -->
            <div>
                <h3 class="text-white font-semibold mb-4">Credits</h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="https://www.hollandsevelden.nl/iconset/" 
                           target="_blank" 
                           rel="dofollow" 
                           class="hover:text-white transition inline-flex items-center gap-1">
                            Hollandse Velden Iconset
                            <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>
                            </svg>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="mt-8 pt-8 border-t border-gray-800 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-gray-500">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Alle rechten voorbehouden.</p>
            <div class="flex gap-4">
                <a href="#" class="hover:text-white transition">Privacy</a>
                <a href="#" class="hover:text-white transition">Voorwaarden</a>
            </div>
        </div>
    </div>
</footer>
