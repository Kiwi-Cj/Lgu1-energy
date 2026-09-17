@php
    $svgFaviconUrl = asset('favicon.svg');
    $svgVersion = substr(sha1((string) @filemtime(public_path('favicon.svg'))), 0, 8);
    $versionedSvgFaviconUrl = $svgFaviconUrl . '?v=' . $svgVersion;

    $faviconUrl = trim((string) ($systemFaviconUrl ?? ''));
    $hasCustomFavicon = $faviconUrl !== '' && !str_ends_with($faviconUrl, 'favicon.svg') && !str_ends_with($faviconUrl, 'favicon.ico');

    if (!$hasCustomFavicon) {
        $faviconUrl = asset('favicon.ico');
    }

    $faviconPath = (string) (parse_url($faviconUrl, PHP_URL_PATH) ?? '');
    $faviconExtension = strtolower((string) pathinfo($faviconPath, PATHINFO_EXTENSION));
    $faviconMime = match ($faviconExtension) {
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'ico' => 'image/x-icon',
        'webp' => 'image/webp',
        'jpg', 'jpeg' => 'image/jpeg',
        default => 'image/x-icon',
    };
    $faviconVersion = substr(sha1($faviconUrl . '_' . ($hasCustomFavicon ? 'custom' : 'default')), 0, 10);
    $versionedFaviconUrl = $faviconUrl . (str_contains($faviconUrl, '?') ? '&' : '?') . 'v=' . $faviconVersion;
@endphp
@if($hasCustomFavicon)
    @if($faviconExtension === 'svg')
        <link rel="icon" type="image/svg+xml" href="{{ $versionedFaviconUrl }}">
    @else
        <link rel="icon" type="{{ $faviconMime }}" href="{{ $versionedFaviconUrl }}">
        <link rel="shortcut icon" type="{{ $faviconMime }}" href="{{ $versionedFaviconUrl }}">
    @endif
    <link rel="apple-touch-icon" href="{{ $versionedFaviconUrl }}">
@else
    <link rel="icon" type="image/svg+xml" href="{{ $versionedSvgFaviconUrl }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v={{ $svgVersion }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v={{ $svgVersion }}">
    <link rel="apple-touch-icon" href="{{ $versionedSvgFaviconUrl }}">
@endif

