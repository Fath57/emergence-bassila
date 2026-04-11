<div>
    <div class="mb-8">
        <p class="text-xs font-semibold text-[#DC143C] uppercase tracking-widest mb-2">Administration</p>
        <h1 class="text-3xl font-bold text-[#111827]">Tableau de bord</h1>
        <p class="text-gray-500 mt-1">Vue d'ensemble de la plateforme Bassila Émergence.</p>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        {{-- Members --}}
        <div class="bg-white border border-gray-200 p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 bottom-0 w-1 bg-[#0066CC]"></div>
            <div class="pl-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Membres</p>
                <p class="text-3xl font-bold text-[#111827] mt-2">{{ number_format($stats['members']) }}</p>
                <p class="text-xs text-[#0066CC] mt-1 font-semibold">+{{ $stats['members_month'] }} ce mois</p>
            </div>
        </div>

        {{-- Profiles --}}
        <div class="bg-white border border-gray-200 p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 bottom-0 w-1 bg-[#DC143C]"></div>
            <div class="pl-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Profils vérifiés</p>
                <p class="text-3xl font-bold text-[#111827] mt-2">{{ $stats['profiles_verified'] }}</p>
                <p class="text-xs text-gray-500 mt-1">sur {{ $stats['profiles'] }} créés</p>
            </div>
        </div>

        {{-- Pending profiles --}}
        <div class="bg-white border border-gray-200 p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 bottom-0 w-1 bg-amber-500"></div>
            <div class="pl-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">À modérer</p>
                <p class="text-3xl font-bold text-[#111827] mt-2">{{ $stats['profiles_pending'] }}</p>
                <p class="text-xs text-amber-600 mt-1 font-semibold">profils en attente</p>
            </div>
        </div>

        {{-- Posts --}}
        <div class="bg-white border border-gray-200 p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 bottom-0 w-1 bg-gray-400"></div>
            <div class="pl-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Articles publiés</p>
                <p class="text-3xl font-bold text-[#111827] mt-2">{{ $stats['posts_published'] }}</p>
                <p class="text-xs text-gray-500 mt-1">+{{ $stats['posts_month'] }} ce mois</p>
            </div>
        </div>

        {{-- Newsletter subscribers --}}
        <div class="bg-white border border-gray-200 p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 bottom-0 w-1 bg-emerald-500"></div>
            <div class="pl-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Abonnés newsletter</p>
                <p class="text-3xl font-bold text-[#111827] mt-2">{{ number_format($stats['newsletter_active']) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $stats['newsletter_pending'] }} en attente</p>
            </div>
        </div>

        {{-- Newsletter campaigns sent --}}
        <div class="bg-white border border-gray-200 p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 bottom-0 w-1 bg-violet-500"></div>
            <div class="pl-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Campagnes envoyées</p>
                <p class="text-3xl font-bold text-[#111827] mt-2">{{ $stats['newsletter_campaigns'] }}</p>
                <p class="text-xs text-gray-500 mt-1">au total</p>
            </div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('admin.profiles') }}" wire:navigate
           class="bg-white border border-gray-200 hover:border-[#0066CC] hover:shadow-sm transition p-5 group">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#0066CC]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                @if ($stats['profiles_pending'] > 0)
                    <span class="text-xs font-bold bg-amber-100 text-amber-800 px-2 py-1 rounded">{{ $stats['profiles_pending'] }}</span>
                @endif
            </div>
            <h3 class="font-bold text-[#111827] mb-1">Modérer les profils</h3>
            <p class="text-sm text-gray-500">Approuver ou rejeter les demandes de vérification.</p>
        </a>

        <a href="{{ route('admin.posts') }}" wire:navigate
           class="bg-white border border-gray-200 hover:border-[#0066CC] hover:shadow-sm transition p-5 group">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#0066CC]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <h3 class="font-bold text-[#111827] mb-1">Gérer les articles</h3>
            <p class="text-sm text-gray-500">Publier, dépublier ou supprimer les articles du blog.</p>
        </a>

        <a href="{{ route('admin.comments') }}" wire:navigate
           class="bg-white border border-gray-200 hover:border-[#0066CC] hover:shadow-sm transition p-5 group">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#0066CC]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                @if ($stats['comments_pending'] > 0)
                    <span class="text-xs font-bold bg-amber-100 text-amber-800 px-2 py-1 rounded">{{ $stats['comments_pending'] }}</span>
                @endif
            </div>
            <h3 class="font-bold text-[#111827] mb-1">Modérer les commentaires</h3>
            <p class="text-sm text-gray-500">Approuver les commentaires en attente.</p>
        </a>

        <a href="{{ route('admin.newsletter') }}" wire:navigate
           class="bg-white border border-gray-200 hover:border-emerald-500 hover:shadow-sm transition p-5 group">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-emerald-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <span class="text-xs font-semibold text-emerald-700">{{ $stats['newsletter_active'] }} abonnés</span>
            </div>
            <h3 class="font-bold text-[#111827] mb-1">Newsletter</h3>
            <p class="text-sm text-gray-500">Gérer les campagnes et les abonnés.</p>
        </a>
    </div>
</div>
