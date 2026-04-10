@extends('layouts.app')

@section('title', 'Le réseau des Bassilais à travers le monde')
@section('description', 'Retrouvez d\'anciens camarades, développez votre réseau professionnel et contribuez à l\'histoire de votre communauté d\'origine.')

@section('content')

{{-- ============================================================
     HERO — photo with dark overlay, full viewport
============================================================ --}}
<section class="relative min-h-[92vh] flex flex-col justify-end overflow-hidden bg-[#0A1628]">

    {{-- Background photo (replace URL with a real community photo of Bassila) --}}
    <div class="absolute inset-0">
        <img
            src="https://images.unsplash.com/photo-1529390079861-591de354faf5?auto=format&fit=crop&w=1600&q=80"
            alt="Communauté"
            class="w-full h-full object-cover object-center"
            loading="eager"
        >
        {{-- Solid dark overlay — no gradient --}}
        <div class="absolute inset-0 bg-[#0A1628]/65"></div>
    </div>

    {{-- Hero content --}}
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pb-16 pt-32">
        <div class="max-w-3xl">
            <p class="text-sm font-semibold text-[#DC143C] uppercase tracking-widest mb-5">
                Plateforme communautaire
            </p>
            <h1 class="text-white leading-tight mb-6"
                style="font-family: 'Lora', serif; font-size: clamp(2.5rem, 5vw, 4rem); font-weight: 700;">
                Le réseau des Bassilais<br>à travers le monde
            </h1>
            <p class="text-white/75 text-lg leading-relaxed mb-10 max-w-xl">
                Retrouvez d'anciens camarades, développez votre réseau professionnel et contribuez à l'histoire de votre communauté d'origine.
            </p>
            <div class="flex flex-wrap gap-4">
                @auth
                    <a href="{{ route('directory.index') }}"
                       class="bg-[#0066CC] hover:bg-blue-800 text-white font-semibold px-7 py-3 text-sm transition">
                        Explorer l'annuaire
                    </a>
                    <a href="{{ route('blog.index') }}"
                       class="border border-white/40 hover:border-white text-white font-semibold px-7 py-3 text-sm transition">
                        Lire le blog
                    </a>
                @else
                    <a href="{{ route('register') }}"
                       class="bg-[#0066CC] hover:bg-blue-800 text-white font-semibold px-7 py-3 text-sm transition">
                        Créer mon profil
                    </a>
                    <a href="{{ route('directory.index') }}"
                       class="border border-white/40 hover:border-white text-white font-semibold px-7 py-3 text-sm transition">
                        Explorer l'annuaire
                    </a>
                @endauth
            </div>
        </div>

        {{-- Stats bar — only shown when platform has data --}}
        @if(collect($stats)->sum() > 0)
            <div class="mt-16 pt-8 border-t border-white/15 grid grid-cols-2 sm:grid-cols-4 gap-6">
                <div>
                    <div class="text-white font-bold text-3xl" style="font-family: 'Lora', serif;">
                        {{ number_format($stats['members']) }}
                    </div>
                    <div class="text-white/50 text-xs uppercase tracking-wider mt-1">Membres inscrits</div>
                </div>
                <div>
                    <div class="text-white font-bold text-3xl" style="font-family: 'Lora', serif;">
                        {{ $stats['profiles'] }}
                    </div>
                    <div class="text-white/50 text-xs uppercase tracking-wider mt-1">Profils vérifiés</div>
                </div>
                <div>
                    <div class="text-white font-bold text-3xl" style="font-family: 'Lora', serif;">
                        {{ $stats['countries'] }}
                    </div>
                    <div class="text-white/50 text-xs uppercase tracking-wider mt-1">Pays représentés</div>
                </div>
                <div>
                    <div class="text-white font-bold text-3xl" style="font-family: 'Lora', serif;">
                        {{ $stats['posts'] }}
                    </div>
                    <div class="text-white/50 text-xs uppercase tracking-wider mt-1">Articles publiés</div>
                </div>
            </div>
        @else
            <div class="mt-16 pt-8 border-t border-white/15">
                <p class="text-white/40 text-sm">Plateforme en cours de lancement — rejoignez les premiers membres.</p>
            </div>
        @endif
    </div>
</section>

{{-- ============================================================
     MISSION
============================================================ --}}
<section class="bg-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-16 items-center">

            <div>
                <p class="text-[#DC143C] text-xs font-semibold uppercase tracking-widest mb-4">Notre mission</p>
                <h2 class="text-3xl font-bold text-[#111827] leading-tight mb-6" style="font-family: 'Lora', serif;">
                    Un lien vivant entre Bassila et sa diaspora
                </h2>
                <p class="text-gray-600 text-base leading-relaxed mb-5">
                    Bassila, ville au cœur du Bénin, a vu grandir des centaines de professionnels aujourd'hui dispersés en Afrique, en Europe et dans le monde entier. EmergenceBassila est leur maison numérique.
                </p>
                <p class="text-gray-600 text-base leading-relaxed mb-8">
                    Chaque profil raconte une histoire de résilience et de réussite. Chaque connexion est un pont entre les générations et les horizons.
                </p>
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 text-[#0066CC] font-semibold text-sm hover:underline">
                    Rejoindre le réseau
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>

            <div class="relative">
                <img
                    src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=900&q=80"
                    alt="Professionnels en réunion"
                    class="w-full aspect-[4/3] object-cover"
                >
                {{-- Accent bar --}}
                <div class="absolute bottom-0 left-0 w-16 h-1 bg-[#DC143C]"></div>
                <div class="absolute bottom-0 left-16 w-8 h-1 bg-[#0066CC]"></div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     3 PILLARS
============================================================ --}}
<section class="bg-gray-50 py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-12">
            <p class="text-[#DC143C] text-xs font-semibold uppercase tracking-widest mb-3">Ce que vous y trouvez</p>
            <h2 class="text-3xl font-bold text-[#111827]" style="font-family: 'Lora', serif;">
                Une plateforme, trois piliers
            </h2>
        </div>

        <div class="grid md:grid-cols-3 gap-6">

            {{-- Réseau --}}
            <div class="bg-white border border-gray-200 p-8">
                <div class="w-10 h-10 border-2 border-[#0066CC] flex items-center justify-center mb-6">
                    <svg class="w-5 h-5 text-[#0066CC]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-[#111827] text-lg mb-3" style="font-family: 'Lora', serif;">Réseau professionnel</h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-6">
                    Créez votre profil, partagez votre parcours et connectez-vous avec les professionnels Bassilais dans votre domaine, où qu'ils soient.
                </p>
                <a href="{{ route('directory.index') }}" class="text-[#0066CC] text-sm font-semibold hover:underline">
                    Explorer l'annuaire &rarr;
                </a>
            </div>

            {{-- Blog --}}
            <div class="bg-[#0066CC] p-8">
                <div class="w-10 h-10 border-2 border-white/40 flex items-center justify-center mb-6">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-white text-lg mb-3" style="font-family: 'Lora', serif;">Blog & Actualités</h3>
                <p class="text-white/75 text-sm leading-relaxed mb-6">
                    Lisez et partagez des histoires inspirantes, des actualités de la communauté, des conseils de carrière et des réflexions sur Bassila.
                </p>
                <a href="{{ route('blog.index') }}" class="text-white text-sm font-semibold hover:underline">
                    Lire les articles &rarr;
                </a>
            </div>

            {{-- Entraide --}}
            <div class="bg-white border border-gray-200 p-8">
                <div class="w-10 h-10 border-2 border-[#DC143C] flex items-center justify-center mb-6">
                    <svg class="w-5 h-5 text-[#DC143C]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-[#111827] text-lg mb-3" style="font-family: 'Lora', serif;">Entraide & Contact</h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-6">
                    Contactez directement les membres, demandez conseil à des experts ou proposez votre aide à la communauté.
                </p>
                <a href="{{ route('register') }}" class="text-[#DC143C] text-sm font-semibold hover:underline">
                    Rejoindre &rarr;
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     RECENT BLOG POSTS
============================================================ --}}
<section class="bg-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-12">
            <div>
                <p class="text-[#DC143C] text-xs font-semibold uppercase tracking-widest mb-3">Blog</p>
                <h2 class="text-3xl font-bold text-[#111827]" style="font-family: 'Lora', serif;">Histoires & Actualités</h2>
            </div>
            <a href="{{ route('blog.index') }}"
               class="text-[#0066CC] text-sm font-semibold hover:underline shrink-0">
                Tous les articles &rarr;
            </a>
        </div>

        @if($recentPosts->isNotEmpty())
            <div class="grid md:grid-cols-3 gap-6">
                @foreach($recentPosts as $post)
                    <article class="border border-gray-200 group">
                        {{-- Image --}}
                        <div class="aspect-[16/9] overflow-hidden bg-[#0066CC]">
                            @if($post->featured_image_url)
                                <img src="{{ $post->featured_image_url }}"
                                     alt="{{ $post->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-10 h-10 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div class="p-6">
                            @if($post->category)
                                <span class="text-xs font-semibold text-[#0066CC] uppercase tracking-wider">{{ $post->category->name }}</span>
                            @endif
                            <h3 class="font-bold text-[#111827] mt-2 mb-2 leading-snug group-hover:text-[#0066CC] transition"
                                style="font-family: 'Lora', serif;">
                                <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                            </h3>
                            @if($post->excerpt)
                                <p class="text-gray-500 text-sm leading-relaxed line-clamp-2 mb-4">{{ $post->excerpt }}</p>
                            @endif
                            <div class="flex items-center gap-2 text-xs text-gray-400 border-t border-gray-100 pt-4">
                                <span>{{ $post->published_at?->format('d M Y') }}</span>
                                <span>·</span>
                                <span>{{ $post->reading_time }} min</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="border border-gray-200 p-16 text-center">
                <p class="text-gray-500 text-sm mb-4">Les premiers articles de la communauté arrivent bientôt.</p>
                @auth
                    <a href="{{ route('blog.create') }}"
                       class="text-[#0066CC] text-sm font-semibold hover:underline">
                        Rédiger le premier article
                    </a>
                @else
                    <a href="{{ route('register') }}"
                       class="text-[#0066CC] text-sm font-semibold hover:underline">
                        Rejoindre et contribuer
                    </a>
                @endauth
            </div>
        @endif
    </div>
</section>

{{-- ============================================================
     FEATURED PROFILES
============================================================ --}}
<section class="bg-gray-50 py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-12">
            <div>
                <p class="text-[#DC143C] text-xs font-semibold uppercase tracking-widest mb-3">Communauté</p>
                <h2 class="text-3xl font-bold text-[#111827]" style="font-family: 'Lora', serif;">Quelques membres</h2>
            </div>
            <a href="{{ route('directory.index') }}"
               class="text-[#0066CC] text-sm font-semibold hover:underline shrink-0">
                Voir l'annuaire complet &rarr;
            </a>
        </div>

        @if($featuredProfiles->isNotEmpty())
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($featuredProfiles as $profile)
                    <a href="{{ route('profile.show', $profile) }}"
                       class="bg-white border border-gray-200 p-5 flex items-start gap-4 hover:border-[#0066CC] transition group">
                        {{-- Avatar --}}
                        @if($profile->avatar_url)
                            <img src="{{ $profile->avatar_url }}"
                                 alt="{{ $profile->full_name }}"
                                 class="w-12 h-12 object-cover shrink-0">
                        @else
                            <div class="w-12 h-12 bg-[#0066CC] flex items-center justify-center text-white font-bold shrink-0">
                                {{ strtoupper(substr($profile->full_name, 0, 1)) }}
                            </div>
                        @endif
                        {{-- Info --}}
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="font-semibold text-[#111827] text-sm truncate group-hover:text-[#0066CC] transition">
                                    {{ $profile->full_name }}
                                </span>
                                @if($profile->is_verified)
                                    <svg class="w-3.5 h-3.5 shrink-0 text-[#0066CC]" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>
                            @if($profile->job_title)
                                <p class="text-gray-500 text-xs mt-0.5 truncate">{{ $profile->job_title }}</p>
                            @endif
                            @if($profile->city || $profile->country)
                                <p class="text-gray-400 text-xs mt-0.5">
                                    {{ collect([$profile->city, $profile->country])->filter()->implode(', ') }}
                                </p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="border border-gray-200 p-16 text-center bg-white">
                <p class="text-gray-500 text-sm mb-4">Soyez parmi les premiers membres de la communauté.</p>
                <a href="{{ route('register') }}"
                   class="bg-[#0066CC] hover:bg-blue-800 text-white font-semibold px-6 py-2.5 text-sm transition inline-block">
                    Créer mon profil
                </a>
            </div>
        @endif
    </div>
</section>

{{-- ============================================================
     CTA FINAL
============================================================ --}}
<section class="bg-[#0066CC] py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <p class="text-white/60 text-xs font-semibold uppercase tracking-widest mb-4">Rejoindre</p>
            <h2 class="text-white font-bold leading-tight mb-6"
                style="font-family: 'Lora', serif; font-size: clamp(1.75rem, 3vw, 2.5rem);">
                Votre histoire fait partie de l'héritage de Bassila
            </h2>
            <p class="text-white/75 text-base leading-relaxed mb-8 max-w-lg">
                Créez votre profil gratuitement, partagez votre parcours et rejoignez les Bassilais épanouis à travers le monde.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('register') }}"
                   class="bg-white text-[#0066CC] font-semibold px-7 py-3 text-sm hover:bg-gray-100 transition">
                    Créer mon profil — gratuit
                </a>
                <a href="{{ route('login') }}"
                   class="border border-white/40 hover:border-white text-white font-semibold px-7 py-3 text-sm transition">
                    J'ai déjà un compte
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
