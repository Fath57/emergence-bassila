<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $pageTitle       = $__env->yieldContent('title');
        $pageDescription = $__env->yieldContent('description') ?: 'La plateforme de networking des Bassilais à travers le monde.';
        $fullTitle       = $pageTitle ? $pageTitle . ' — EmergenceBassila' : 'EmergenceBassila';
    @endphp

    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="EmergenceBassila">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $fullTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lora:400,600,700|source-sans-3:400,400i,600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body {
            font-family: 'Source Sans 3', 'Source Sans Pro', sans-serif;
            background-color: #F9FAFB;
            color: #111827;
        }
        h1, h2, h3, h4, .font-serif {
            font-family: 'Lora', Georgia, serif;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col">

    @include('partials.nav')

    @if (session('success'))
        <div class="bg-green-50 border-b border-green-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                <p class="text-sm text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border-b border-red-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                <p class="text-sm text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <main class="flex-1">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    @include('partials.footer')

    @livewireScripts
</body>
</html>
