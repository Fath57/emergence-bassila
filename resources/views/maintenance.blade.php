<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance — Bassila Émergence</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lora:400,500,600,700|source-sans-3:400,400i,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="antialiased">
<div class="min-h-screen flex">

    <div class="hidden lg:flex lg:w-5/12 xl:w-2/5 bg-[#0A1628] flex-col justify-between relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none"
             style="background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                                      linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
                    background-size: 48px 48px;"></div>
        <div class="absolute top-0 left-0 bottom-0 w-0.5 bg-[#DC143C]"></div>

        <div class="relative px-12 pt-14">
            <img src="{{ asset('images/logo.png') }}" alt="Bassila Émergence" class="h-14 w-auto mb-16">
            <h2 class="text-white/80 text-2xl font-bold leading-tight">
                Maintenance en cours
            </h2>
        </div>
    </div>

    <div class="flex-1 flex flex-col bg-white min-h-screen">
        <header class="lg:hidden border-b border-gray-100 h-14 px-6 flex items-center">
            <img src="{{ asset('images/logo-trans.png') }}" alt="Bassila Émergence" class="h-8 w-auto">
        </header>

        <main class="flex-1 flex items-center justify-center px-8 py-12">
            <div class="w-full max-w-md text-center">
                <div class="w-14 h-14 border-2 border-[#0066CC] flex items-center justify-center mx-auto mb-6">
                    <svg class="w-7 h-7 text-[#0066CC]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-[#111827] mb-3">Site en maintenance</h1>
                <p class="text-gray-500 leading-relaxed">{{ $message }}</p>
            </div>
        </main>

        <footer class="border-t border-gray-100 py-4">
            <p class="text-center text-xs text-gray-400">© {{ date('Y') }} Bassila Émergence. Tous droits réservés.</p>
        </footer>
    </div>
</div>
</body>
</html>
