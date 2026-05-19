<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'BLOMFREE') }}</title>
        <meta name="description" content="BLOMFREE & CO. NIG. LTD — your trusted Nigerian partner for land, premium livestock, fashion and gadgets." />
        <meta property="og:site_name" content="{{ config('app.name', 'BLOMFREE') }}" />
        <meta name="theme-color" content="#1A1A1A" />
        <link rel="icon" type="image/x-icon" href="/favicon.ico" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

        @if ($ga = env('GA_MEASUREMENT_ID'))
            {{-- Google Analytics 4 --}}
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', @json($ga));
            </script>
        @endif

        @if ($pixel = env('FB_PIXEL_ID'))
            {{-- Facebook Pixel --}}
            <script>
                !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
                n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
                (window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
                fbq('init', @json($pixel));
                fbq('track', 'PageView');
            </script>
        @endif

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-brand-orange focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
            Skip to content
        </a>
        @inertia
    </body>
</html>
