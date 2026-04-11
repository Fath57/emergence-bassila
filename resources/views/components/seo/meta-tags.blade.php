<title>{{ $fullTitle() }}</title>
<meta name="description" content="{{ $seo->description }}">
<link rel="canonical" href="{{ $seo->canonical }}">

@if ($seo->noindex)
    <meta name="robots" content="noindex, nofollow">
@endif

@if (config('seo.google_verification'))
    <meta name="google-site-verification" content="{{ config('seo.google_verification') }}">
@endif

{{-- Open Graph --}}
<meta property="og:title" content="{{ $fullTitle() }}">
<meta property="og:description" content="{{ $seo->description }}">
<meta property="og:url" content="{{ $seo->canonical }}">
<meta property="og:type" content="{{ $seo->ogType }}">
<meta property="og:site_name" content="{{ config('seo.site_name') }}">
<meta property="og:locale" content="{{ $seo->locale }}">

@php $ogImage = $absoluteOgImage(); @endphp
@if ($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    @if ($seo->ogImageAlt)
        <meta property="og:image:alt" content="{{ $seo->ogImageAlt }}">
    @endif
@endif

@if ($seo->ogType === 'article' && ! empty($seo->articleMeta))
    @isset ($seo->articleMeta['publishedTime'])
        <meta property="article:published_time" content="{{ $seo->articleMeta['publishedTime'] }}">
    @endisset
    @isset ($seo->articleMeta['modifiedTime'])
        <meta property="article:modified_time" content="{{ $seo->articleMeta['modifiedTime'] }}">
    @endisset
    @isset ($seo->articleMeta['author'])
        <meta property="article:author" content="{{ $seo->articleMeta['author'] }}">
    @endisset
    @isset ($seo->articleMeta['section'])
        <meta property="article:section" content="{{ $seo->articleMeta['section'] }}">
    @endisset
    @isset ($seo->articleMeta['tags'])
        @foreach ((array) $seo->articleMeta['tags'] as $tag)
            <meta property="article:tag" content="{{ $tag }}">
        @endforeach
    @endisset
@endif

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $fullTitle() }}">
<meta name="twitter:description" content="{{ $seo->description }}">
@if ($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
    @if ($seo->ogImageAlt)
        <meta name="twitter:image:alt" content="{{ $seo->ogImageAlt }}">
    @endif
@endif
