<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        use App\Support\Seo\SeoData;

        $seo = $seo ?? SeoData::default();

        // Allow views using @section('title') / @section('description') to override the DTO.
        $yieldedTitle = trim($__env->yieldContent('title'));
        $yieldedDesc  = trim($__env->yieldContent('description'));
        if ($yieldedTitle !== '') {
            $seo = $seo->withTitle($yieldedTitle);
        }
        if ($yieldedDesc !== '') {
            $seo = $seo->withDescription($yieldedDesc);
        }

        $seo = $seo->withCanonical(url()->current());
    @endphp

    <x-seo.meta-tags :seo="$seo" />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preload" as="style" href="https://fonts.bunny.net/css?family=lora:400,500,600,700|source-sans-3:400,400i,600,700&display=swap">
    <link href="https://fonts.bunny.net/css?family=lora:400,500,600,700|source-sans-3:400,400i,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')

    {{-- Body background is page-specific; keep the only rule that can't
         live in app.css because it's tied to the app (not the guest) layout --}}
    <style>
        body { background-color: #F9FAFB; }
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
