
@php
    $faviconUrl = trim((string) ($systemFaviconUrl ?? asset('img/logocityhall.jpg')));
    $faviconPath = (string) (parse_url($faviconUrl, PHP_URL_PATH) ?? '');
    $faviconExtension = strtolower((string) pathinfo($faviconPath, PATHINFO_EXTENSION));
    $faviconMime = match ($faviconExtension) {
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'ico' => 'image/x-icon',
        'webp' => 'image/webp',
        default => 'image/jpeg',
    };
    $faviconVersion = substr(sha1($faviconUrl), 0, 10);
    $versionedFaviconUrl = $faviconUrl.($faviconUrl !== '' && str_contains($faviconUrl, '?') ? '&' : '?').'v='.$faviconVersion;
@endphp
<link rel="icon" type="{{ $faviconMime }}" href="{{ $versionedFaviconUrl }}">
<link rel="shortcut icon" type="{{ $faviconMime }}" href="{{ $versionedFaviconUrl }}">
<link rel="apple-touch-icon" href="{{ $versionedFaviconUrl }}">
