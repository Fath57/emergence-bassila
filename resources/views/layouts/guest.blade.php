<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Authentification' }} — Bassila Émergence</title>
    <meta name="description" content="Authentification sur Bassila Émergence — le réseau des Bassilois à travers le monde.">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="{{ url()->current() }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lora:400,500,600,700|source-sans-3:400,400i,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased">

<div class="min-h-screen flex">

    {{-- ── Left: brand identity panel (desktop only) ────────────────── --}}
    <div class="hidden lg:flex lg:w-5/12 xl:w-2/5 bg-[#0A1628] flex-col justify-between relative overflow-hidden">

        {{-- Subtle grid texture --}}
        <div class="absolute inset-0 pointer-events-none"
             style="background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                                      linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
                    background-size: 48px 48px;"></div>

        {{-- Left accent stripe --}}
        <div class="absolute top-0 left-0 bottom-0 w-0.5 bg-[#DC143C]"></div>

        {{-- Top-right blue accent --}}
        <div class="absolute top-0 right-0 w-20 h-0.5 bg-[#0066CC]"></div>

        {{-- Brand & headline --}}
        <div class="relative px-12 pt-14">
            <a href="{{ url('/') }}" class="inline-block mb-16 group">
                <img src="{{ asset('images/logo.png') }}"
                     alt="Bassila Émergence"
                     class="h-14 w-auto">
            </a>

            <h2 class="text-white/80 leading-tight mb-5"
                style="font-family: 'Lora', serif; font-size: clamp(1.5rem, 2.5vw, 2rem); font-weight: 700;">
                La communauté des<br>Bassilois dans le monde
            </h2>

            <p class="text-white/35 text-sm leading-relaxed max-w-xs">
                Retrouvez vos camarades, développez votre réseau professionnel et contribuez à l'histoire de votre communauté.
            </p>
        </div>

        {{-- Bottom testimonial --}}
        <div class="relative px-12 pb-12">
            <div class="border-l-2 border-[#DC143C] pl-5">
                <p class="text-white/40 text-sm italic leading-relaxed mb-2">
                    "Connectés, engagés, inspirants."
                </p>
                <span class="text-white/20 text-xs uppercase tracking-widest font-semibold">Bassila Émergence</span>
            </div>
        </div>
    </div>

    {{-- ── Right: form panel ──────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col bg-white min-h-screen">

        {{-- Mobile-only logo header --}}
        <header class="lg:hidden border-b border-gray-100 h-14 px-6 flex items-center">
            <a href="{{ url('/') }}" class="flex items-center">
                <img src="{{ asset('images/logo-trans.png') }}"
                     alt="Bassila Émergence"
                     class="h-8 w-auto">
            </a>
        </header>

        <main class="flex-1 flex items-center justify-center px-8 py-12">
            <div class="w-full max-w-sm">
                {{ $slot }}
            </div>
        </main>

        <footer class="border-t border-gray-100 py-4">
            <p class="text-center text-xs text-gray-400">© {{ date('Y') }} Bassila Émergence. Tous droits réservés.</p>
        </footer>
    </div>
</div>

@livewireScripts
</body>
</html>
