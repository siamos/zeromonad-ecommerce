@php
    $settings = app(\App\Settings\GeneralSettings::class);
    $siteName = $settings->site_name ?? config('app.name');
    $siteDesc = $settings->site_description ?? '';
    $siteLogoUrl = $settings->site_logo
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($settings->site_logo)
        : null;
    $currentUrl = request()->url();
    $locale = app()->getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ $siteName }}</title>

    {{-- Default SEO meta (Inertia Head overrides per-page) --}}
    @if($siteDesc)
    <meta name="description" content="{{ $siteDesc }}">
    @endif
    <link rel="canonical" href="{{ $currentUrl }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $currentUrl }}">
    <meta property="og:title" content="{{ $siteName }}">
    @if($siteDesc)
    <meta property="og:description" content="{{ $siteDesc }}">
    @endif
    <meta property="og:site_name" content="{{ $siteName }}">
    @if($siteLogoUrl)
    <meta property="og:image" content="{{ $siteLogoUrl }}">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $siteName }}">
    @if($siteDesc)
    <meta name="twitter:description" content="{{ $siteDesc }}">
    @endif
    @if($siteLogoUrl)
    <meta name="twitter:image" content="{{ $siteLogoUrl }}">
    @endif

    {{-- Hreflang for bilingual (el / en) --}}
    <link rel="alternate" hreflang="el" href="{{ str_replace(request()->root(), request()->root(), $currentUrl) }}">
    <link rel="alternate" hreflang="en" href="{{ $currentUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ $currentUrl }}">

    {{-- JSON-LD: WebSite + Organization --}}
    @php
        $jsonLd = json_encode([
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $siteName,
                'url' => config('app.url'),
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => route('shop') . '?search={search_term_string}',
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
            array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => $siteName,
                'url' => config('app.url'),
                'logo' => $siteLogoUrl,
            ]),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @endphp
    <script type="application/ld+json">{!! $jsonLd !!}</script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
