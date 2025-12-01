<footer class="bg-gray-900 text-gray-300 mt-auto">
    <div class="mx-auto max-w-6xl px-6 py-12">
        <div class="flex flex-col md:flex-row md:justify-center gap-8 max-w-5xl mx-auto items-start">
            <!-- About / Branding -->
            <div class="flex-shrink-0 md:max-w-xl">
                <div class="flex items-center gap-4 mb-4">
                    <img src="{{ Vite::asset('resources/images/logo.webp') }}" alt="Logo {{ config('app.name') }}" class="h-16">
                    <h3 class="text-white font-semibold text-2xl">{{ config('app.name') }}</h3>
                </div>
                <p class="text-sm text-gray-400 leading-relaxed mb-2">Voor jeugdtrainers (JO8–JO12) die spelen in 4 kwarten. Automatische, eerlijke line-ups & slimme rotatie zodat jij kunt focussen op coaching.</p>
            </div>

            <!-- Open Source / Support -->
            <div class="flex-shrink-0 md:max-w-md">
                <h3 class="text-white font-semibold mb-4">Bijdragen & Steun</h3>
                <div class="space-y-3 flex flex-col">
                    <a href="https://github.com/antwanvdm/vvor-team-manager" target="_blank" rel="noopener noreferrer" class="w-fit inline-flex items-center gap-2 px-4 py-2 rounded-md bg-gray-800 hover:bg-gray-700 text-sm font-medium">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.6.111.793-.261.793-.577v-2.07c-3.338.726-4.033-1.416-4.033-1.416-.55-1.387-1.34-1.756-1.34-1.756-1.09-.745.082-.729.082-.729 1.206.084 1.84 1.237 1.84 1.237 1.069 1.834 2.806 1.304 3.49.997.108-.775.42-1.304.764-1.603-2.665-.305-5.466-1.334-5.466-5.93 0-1.311.468-2.382 1.235-3.222-.124-.303-.536-1.524.117-3.176 0 0 1.008-.322 3.302 1.23.956-.266 1.982-.399 3.002-.404 1.02.005 2.047.138 3.004.404 2.292-1.552 3.299-1.23 3.299-1.23.653 1.653.241 2.874.117 3.176.77.84 1.236 1.911 1.236 3.222 0 4.609-2.804 5.624-5.476 5.93.431.372.824 1.101.824 2.221v2.621c0 .319.192.694.8.576C20.565 21.8 24 17.302 24 12c0-6.627-5.373-12-12-12Z"/></svg>
                        Draag bij op GitHub
                    </a>
                    <a href="https://github.com/sponsors/antwanvdm" target="_blank" rel="noopener noreferrer" class="w-fit inline-flex items-center gap-2 px-4 py-2 rounded-md bg-pink-600 hover:bg-pink-500 text-sm font-medium text-white">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        Steun ontwikkeling
                    </a>
                    <a href="https://www.hollandsevelden.nl/iconset/" target="_blank" rel="dofollow" class="inline-flex items-center gap-1 text-sm hover:text-white transition">
                        Hollandse Velden Iconset
                        <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="mt-8 pt-8 border-t border-gray-800 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-gray-500">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Alle rechten voorbehouden.</p>
            <div class="flex flex-wrap gap-4 items-center">
                <a href="{{ route('privacy') }}" class="hover:text-white transition">Privacy</a>
                <a href="https://github.com/antwanvdm/vvor-team-manager" target="_blank" class="text-gray-400 hover:text-white transition">GitHub</a>
                <a href="https://github.com/sponsors/antwanvdm" target="_blank" class="text-pink-500 hover:text-pink-400 transition">Sponsor</a>
            </div>
        </div>
    </div>
</footer>
