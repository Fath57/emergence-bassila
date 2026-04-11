@section('title', 'Annuaire des Bassilais — Diaspora et professionnels')
@section('description', 'Retrouvez les professionnels Bassilais du Bénin et de la diaspora. Filtrez par secteur, pays, compétences et contactez directement les membres vérifiés.')

<div>

{{-- Page header --}}
<div class="bg-[#0A1628] relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none"
         style="background-image: linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                                  linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
                background-size: 48px 48px;"></div>
    <div class="absolute bottom-0 left-0 right-0 h-px bg-[#DC143C] opacity-60"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 relative">
        <h1 class="text-2xl font-bold text-white mb-1">Annuaire des membres</h1>
        <p class="text-sm text-white/40">Découvrez et connectez-vous avec les membres de la communauté Bassila Émergence</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Sidebar Filters --}}
        <aside class="lg:w-60 shrink-0">
            <div class="bg-white border border-gray-200 p-5 space-y-5">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-xs text-[#111827] uppercase tracking-wider">Filtres</h2>
                    <button wire:click="resetFilters" class="text-xs text-[#0066CC] hover:underline font-medium">
                        Réinitialiser
                    </button>
                </div>

                {{-- Sector --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Secteur</label>
                    <select wire:model.live="sector"
                            class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] bg-white">
                        <option value="">Tous les secteurs</option>
                        @foreach ($sectors as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Country --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Pays</label>
                    <input wire:model.live.debounce.400ms="country"
                           type="text"
                           placeholder="Ex : Bénin, France..."
                           class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC]">
                </div>

                {{-- Education years --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Promotion (fin d'études)</label>
                    <div class="flex gap-2">
                        <input wire:model.live.debounce.400ms="yearFrom"
                               type="number" min="1950" max="{{ date('Y') }}" placeholder="De"
                               class="w-1/2 border border-gray-300 px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC]">
                        <input wire:model.live.debounce.400ms="yearTo"
                               type="number" min="1950" max="{{ date('Y') }}" placeholder="À"
                               class="w-1/2 border border-gray-300 px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC]">
                    </div>
                </div>

                {{-- Skills --}}
                @if($availableSkills->count() > 0)
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Compétences</label>
                        <div class="space-y-1.5 max-h-40 overflow-y-auto">
                            @foreach ($availableSkills as $skill)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox"
                                           wire:model.live="skills"
                                           value="{{ $skill->id }}"
                                           class="rounded border-gray-300 text-[#0066CC] focus:ring-[#0066CC]">
                                    <span class="text-sm text-gray-700">{{ $skill->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Verified only --}}
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox"
                               wire:model.live="verifiedOnly"
                               class="rounded border-gray-300 text-[#0066CC] focus:ring-[#0066CC]">
                        <span class="text-sm text-gray-700">Membres vérifiés uniquement</span>
                    </label>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 min-w-0">

            {{-- Search bar --}}
            <div class="mb-5">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input wire:model.live.debounce.300ms="query"
                           type="search"
                           placeholder="Rechercher par nom, poste, entreprise..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] bg-white">
                    <div wire:loading wire:target="query" class="absolute right-3 top-1/2 -translate-y-1/2">
                        <svg class="animate-spin h-4 w-4 text-[#0066CC]" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Results count --}}
            <div class="mb-5 text-sm text-gray-500">
                <span wire:loading.remove>{{ $profiles->total() }} membre(s) trouvé(s)</span>
                <span wire:loading class="text-gray-400">Recherche en cours...</span>
            </div>

            {{-- Profile grid --}}
            @if ($profiles->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4" wire:loading.class="opacity-60">
                    @foreach ($profiles as $profile)
                        <livewire:profile.profile-card :profile="$profile" :key="$profile->id" />
                    @endforeach
                </div>
                <div class="mt-8">
                    {{ $profiles->links() }}
                </div>
            @else
                <div class="border border-gray-200 p-16 text-center bg-white">
                    <p class="text-gray-500 text-sm mb-3">Aucun membre trouvé avec ces critères.</p>
                    <button wire:click="resetFilters" class="text-sm text-[#0066CC] hover:underline font-medium">
                        Effacer les filtres
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
</div>
