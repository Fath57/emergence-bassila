<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Administration' }} — Bassila Émergence</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lora:400,500,600,700|source-sans-3:400,400i,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body { background-color: #F5F5F5; }
    </style>
</head>
<body class="antialiased">

<div class="min-h-screen flex">

    {{-- ── Sidebar ────────────────────────────────────────────────── --}}
    <aside class="w-64 bg-[#0A1628] text-white flex-shrink-0 hidden lg:flex flex-col">
        {{-- Logo --}}
        <div class="px-6 py-6 border-b border-white/10">
            <a href="{{ route('admin.dashboard') }}" class="inline-block">
                <img src="{{ asset('images/logo.png') }}"
                     alt="Bassila Émergence"
                     class="h-10 w-auto">
            </a>
            <p class="text-[10px] uppercase tracking-widest text-white/40 mt-3 font-semibold">Administration</p>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 py-6 px-3 space-y-1">
            @php
                $navItems = [
                    ['route' => 'admin.dashboard', 'label' => 'Tableau de bord', 'icon' => 'home'],
                    ['route' => 'admin.profiles',  'label' => 'Profils',         'icon' => 'users'],
                    ['route' => 'admin.posts',     'label' => 'Articles',        'icon' => 'document'],
                    ['route' => 'admin.comments',  'label' => 'Commentaires',    'icon' => 'chat'],
                    ['route' => 'admin.users',     'label' => 'Utilisateurs',    'icon' => 'user-plus'],
                    ['route' => 'admin.roles',     'label' => 'Rôles',           'icon' => 'shield'],
                    ['route' => 'admin.settings',  'label' => 'Paramètres',      'icon' => 'cog'],
                ];
            @endphp

            @foreach ($navItems as $item)
                @php $active = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   wire:navigate
                   class="flex items-center gap-3 px-3 py-2.5 text-sm font-semibold transition {{ $active ? 'bg-[#0066CC] text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                    @if ($item['icon'] === 'home')
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    @elseif ($item['icon'] === 'users')
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    @elseif ($item['icon'] === 'document')
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    @elseif ($item['icon'] === 'chat')
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    @elseif ($item['icon'] === 'cog')
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    @elseif ($item['icon'] === 'user-plus')
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    @elseif ($item['icon'] === 'shield')
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    @endif
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-white/10">
            <div class="text-xs text-white/50 mb-3">
                Connecté en tant que<br>
                <span class="text-white font-semibold">{{ auth()->user()->name }}</span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('home') }}"
                   class="flex-1 text-center text-xs py-2 border border-white/10 text-white/70 hover:text-white hover:border-white/30 transition">
                    ← Retour au site
                </a>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="w-full text-xs py-2 border border-white/10 text-white/70 hover:text-[#DC143C] hover:border-[#DC143C]/40 transition">
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>

    {{-- ── Main content ───────────────────────────────────────────── --}}
    <main class="flex-1 min-w-0">
        {{-- Mobile header (lg:hidden) --}}
        <header class="lg:hidden bg-[#0A1628] text-white px-5 py-4 flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/logo.png') }}" alt="Bassila Émergence" class="h-8 w-auto">
                <span class="text-xs uppercase tracking-widest text-white/50 font-semibold">Admin</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs text-white/70 hover:text-[#DC143C]">Déconnexion</button>
            </form>
        </header>

        @if (session('success'))
            <div class="bg-green-50 border-b border-green-200 px-6 py-3">
                <p class="text-sm text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border-b border-red-200 px-6 py-3">
                <p class="text-sm text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        <div class="p-6 lg:p-10 max-w-7xl mx-auto">
            {{ $slot }}
        </div>
    </main>
</div>

@livewireScripts
</body>
</html>
