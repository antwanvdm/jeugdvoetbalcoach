@php($gaId = config('services.ga.measurement_id'))
@if ($gaId)
    {{-- Google Analytics (GA4) met Consent Mode v2 --}}
    {{-- Laadt altijd, maar denied by default voor ads/personalization --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        
        // Consent Mode v2: denied by default voor ads/personalization
        gtag('consent', 'default', {
            'ad_storage': 'denied',
            'ad_user_data': 'denied',
            'ad_personalization': 'denied',
            'analytics_storage': 'granted',  // Analytics wel toegestaan
            'functionality_storage': 'granted',
            'personalization_storage': 'denied',
            'security_storage': 'granted'
        });
        
        gtag('js', new Date());
        gtag('config', '{{ $gaId }}', {
            'anonymize_ip': true,  // IP anonimiseren
            'cookie_flags': 'SameSite=None;Secure'  // Alleen met HTTPS
        });
    </script>
@endif
