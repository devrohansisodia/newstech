@props([
    'seo',
])

<title>{{ $seo->title }}</title>
<meta name="description" content="{{ $seo->description }}">
<link rel="canonical" href="{{ $seo->canonicalUrl }}">
<meta name="robots" content="{{ $seo->robots }}">

<meta property="og:title" content="{{ $seo->resolvedOpenGraphTitle() }}">
<meta property="og:description" content="{{ $seo->resolvedOpenGraphDescription() }}">
<meta property="og:url" content="{{ $seo->canonicalUrl }}">
<meta property="og:type" content="website">

@if ($seo->openGraphImage)
    <meta property="og:image" content="{{ $seo->openGraphImage }}">
@endif

<meta name="twitter:card" content="{{ $seo->twitterCard }}">
<meta name="twitter:title" content="{{ $seo->resolvedTwitterTitle() }}">
<meta name="twitter:description" content="{{ $seo->resolvedTwitterDescription() }}">

@if ($seo->twitterImage)
    <meta name="twitter:image" content="{{ $seo->twitterImage }}">
@endif

@foreach ($seo->structuredData as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@endforeach

@if ($seo->breadcrumbStructuredData())
    <script type="application/ld+json">{!! json_encode($seo->breadcrumbStructuredData(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@endif
