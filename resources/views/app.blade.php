@php
    $props = $page['props'] ?? [];
    $meta = $props['meta'] ?? [];
    $site = $props['site'] ?? [];
    $isAdmin = request()->is('admin', 'admin/*');
    $accent = $site['appearance']['accent'] ?? null;
    $theme = $site['appearance']['theme'] ?? 'light';
    $title = $meta['title'] ?? ($site['identity']['name'] ?? config('app.name'));
    $description = $meta['description'] ?? null;
    $image = $meta['image'] ?? null;
    $canonical = $meta['url'] ?? url()->current();
    $verify = $isAdmin ? [] : \App\Support\SiteSettings::group('seo');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ $title }}</title>
    @if ($description)
        <meta inertia="description" name="description" content="{{ $description }}">
    @endif
    @if ($isAdmin || ($meta['noindex'] ?? false))
        <meta name="robots" content="noindex, nofollow">
    @else
        <link rel="canonical" href="{{ $canonical }}">
        <meta property="og:type" content="{{ $meta['type'] ?? 'website' }}">
        <meta property="og:site_name" content="{{ $site['identity']['name'] ?? config('app.name') }}">
        <meta property="og:title" content="{{ $title }}">
        <meta property="og:url" content="{{ $canonical }}">
        @if ($description)
            <meta property="og:description" content="{{ $description }}">
        @endif
        @if ($image)
            <meta property="og:image" content="{{ $image }}">
            <meta name="twitter:card" content="summary_large_image">
        @else
            <meta name="twitter:card" content="summary">
        @endif
        <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) === 'en' ? 'en_GB' : app()->getLocale() }}">
        <meta name="twitter:title" content="{{ $title }}">
        @if ($description)
            <meta name="twitter:description" content="{{ $description }}">
        @endif
        @isset($articleMeta)
            @if ($articleMeta['published'])<meta property="article:published_time" content="{{ $articleMeta['published'] }}">@endif
            @if ($articleMeta['modified'])<meta property="article:modified_time" content="{{ $articleMeta['modified'] }}">@endif
            @if ($articleMeta['section'])<meta property="article:section" content="{{ $articleMeta['section'] }}">@endif
            @foreach ($articleMeta['tags'] as $tag)<meta property="article:tag" content="{{ $tag }}">@endforeach
        @endisset
        @isset($schema)
            <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
        @endisset
        <link rel="alternate" type="application/rss+xml" title="{{ $site['identity']['name'] ?? config('app.name') }}" href="{{ route('feed') }}">
    @endif
    @if (! empty($verify['googleVerification']))
        <meta name="google-site-verification" content="{{ $verify['googleVerification'] }}">
    @endif
    @if (! empty($verify['bingVerification']))
        <meta name="msvalidate.01" content="{{ $verify['bingVerification'] }}">
    @endif
    @if (! empty($verify['gaMeasurementId']))
        {{-- Google Analytics 4: public pages only. Page views are sent on every Inertia navigation (resources/js/app.jsx). --}}
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $verify['gaMeasurementId'] }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', {{ Js::from($verify['gaMeasurementId']) }}, { send_page_view: false, anonymize_ip: true });
        </script>
    @endif
    <meta name="theme-color" content="#F5F4F0">
    @if (! empty($site['identity']['logoUrl']))
        <link rel="icon" href="{{ $site['identity']['logoUrl'] }}">
        <link rel="apple-touch-icon" href="{{ $site['identity']['logoUrl'] }}">
    @else
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    @if ($accent && preg_match('/^#[0-9A-Fa-f]{6}$/', $accent))
        @php([$r, $g, $b] = sscanf($accent, '#%02x%02x%02x'))
        <style>:root{--accent:{{ $accent }};--accent-soft:rgba({{ $r }},{{ $g }},{{ $b }},0.25)}</style>
    @endif
    @unless ($isAdmin)
        <script>
            // Apply the visitor's theme before the page paints. Light is the default.
            try {
                var t = localStorage.getItem('ct-theme');
                var d = {{ Js::from($theme) }};
                if (t === 'dark' || (!t && (d === 'dark' || (d === 'system' && matchMedia('(prefers-color-scheme: dark)').matches)))) {
                    document.documentElement.dataset.theme = 'dark';
                }
            } catch (e) {}
        </script>
    @endunless
    @viteReactRefresh
    @vite(['resources/js/app.jsx', "resources/js/pages/{$page['component']}.jsx"])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
