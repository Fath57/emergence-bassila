<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vérification email — EmergenceBassila</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lora:400,700|source-sans-3:400,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Source Sans 3', sans-serif; color: #111827; }
        h1, h2 { font-family: 'Lora', serif; }
    </style>
</head>
<body class="antialiased">

<div class="min-h-screen flex">

    {{-- Left brand panel --}}
    <div class="hidden lg:flex lg:w-5/12 xl:w-2/5 bg-[#0A1628] flex-col justify-between relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none"
             style="background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                                      linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
                    background-size: 48px 48px;"></div>
        <div class="absolute top-0 left-0 bottom-0 w-0.5 bg-[#DC143C]"></div>
        <div class="absolute top-0 right-0 w-20 h-0.5 bg-[#0066CC]"></div>

        <div class="relative px-12 pt-14">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-0.5 mb-16">
                <span class="font-bold text-2xl text-[#0066CC]" style="font-family: 'Lora', serif;">Emergence</span><span
                    class="font-bold text-2xl text-[#DC143C]" style="font-family: 'Lora', serif;">Bassila</span>
            </a>
            <h2 class="text-white/80 text-2xl font-bold leading-tight mb-5" style="font-family: 'Lora', serif;">
                Bienvenue dans la communauté
            </h2>
            <p class="text-white/35 text-sm leading-relaxed max-w-xs">
                Plus qu'une étape avant de rejoindre les Bassilais du monde entier.
            </p>
        </div>

        <div class="relative px-12 pb-12">
            <div class="border-l-2 border-[#DC143C] pl-5">
                <p class="text-white/40 text-sm italic">"Connectés, engagés, inspirants."</p>
                <span class="text-white/20 text-xs uppercase tracking-widest font-semibold">EmergenceBassila</span>
            </div>
        </div>
    </div>

    {{-- Right panel --}}
    <div class="flex-1 flex flex-col bg-white min-h-screen">

        <header class="lg:hidden border-b border-gray-100 h-14 px-6 flex items-center">
            <a href="{{ url('/') }}" class="flex items-center gap-0.5">
                <span class="font-bold text-lg text-[#0066CC]" style="font-family: 'Lora', serif;">Emergence</span><span
                    class="font-bold text-lg text-[#DC143C]" style="font-family: 'Lora', serif;">Bassila</span>
            </a>
        </header>

        <main class="flex-1 flex items-center justify-center px-8 py-12">
            <div class="w-full max-w-sm text-center">

                <div class="w-14 h-14 border-2 border-[#0066CC] flex items-center justify-center mx-auto mb-6">
                    <svg class="w-7 h-7 text-[#0066CC]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-[#111827] mb-3" style="font-family: 'Lora', serif;">
                    Vérifiez votre email
                </h1>
                <p class="text-sm text-gray-500 leading-relaxed mb-8">
                    Avant de continuer, veuillez cliquer sur le lien de vérification envoyé à votre adresse email. Si vous ne l'avez pas reçu, cliquez ci-dessous pour en envoyer un nouveau.
                </p>

                @if (session('status') === 'verification-link-sent')
                    <div class="bg-green-50 border border-green-200 px-4 py-3 mb-6">
                        <p class="text-sm font-medium text-green-800">Un nouveau lien de vérification a été envoyé.</p>
                    </div>
                @endif

                <div class="flex flex-col gap-3">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit"
                                class="w-full bg-[#0066CC] hover:bg-blue-800 text-white font-semibold text-sm px-5 py-3 transition">
                            Renvoyer l'email de vérification
                        </button>
                    </form>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full border border-gray-200 hover:border-gray-300 text-gray-600 font-semibold text-sm px-5 py-3 transition">
                            Se déconnecter
                        </button>
                    </form>
                </div>
            </div>
        </main>

        <footer class="border-t border-gray-100 py-4">
            <p class="text-center text-xs text-gray-400">© {{ date('Y') }} EmergenceBassila. Tous droits réservés.</p>
        </footer>
    </div>
</div>

</body>
</html>
