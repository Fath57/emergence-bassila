<div>
    <div class="mb-8">
        <p class="text-xs font-semibold text-[#DC143C] uppercase tracking-widest mb-2">Modération</p>
        <h1 class="text-3xl font-bold text-[#111827]">Profils</h1>
        <p class="text-gray-500 mt-1">Approuvez ou rejetez les demandes de vérification.</p>
    </div>

    {{-- Filters bar --}}
    <div class="bg-white border border-gray-200 p-4 mb-6 flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>
            </svg>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Rechercher par nom, poste, entreprise…"
                   class="w-full border border-gray-200 pl-10 pr-4 py-2 text-sm focus:outline-none focus:border-[#0066CC]">
        </div>
        <div class="flex gap-1 bg-gray-50 p-1 border border-gray-200">
            @foreach (['pending' => 'En attente', 'verified' => 'Vérifiés', 'all' => 'Tous'] as $value => $label)
                <button type="button"
                        wire:click="$set('filter', '{{ $value }}')"
                        class="text-xs font-semibold px-3 py-1.5 transition {{ $filter === $value ? 'bg-[#0066CC] text-white' : 'text-gray-600 hover:text-[#111827]' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Membre</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Poste</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Secteur · Pays</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Créé</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($profiles as $profile)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $profile->avatar_url }}" alt="" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                                <div class="min-w-0">
                                    <p class="font-semibold text-[#111827] truncate">{{ $profile->full_name }}</p>
                                    <p class="text-xs text-gray-400 truncate">{{ $profile->user?->email }}</p>
                                </div>
                                @if ($profile->is_verified)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 bg-green-50 border border-green-100 px-2 py-0.5">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        Vérifié
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 hidden md:table-cell">
                            <p class="text-sm text-[#111827]">{{ $profile->job_title }}</p>
                            @if ($profile->company)
                                <p class="text-xs text-gray-400">{{ $profile->company }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-4 hidden lg:table-cell">
                            <p class="text-sm text-gray-600">{{ $profile->sector?->name ?? '—' }}</p>
                            <p class="text-xs text-gray-400">{{ $profile->country }}</p>
                        </td>
                        <td class="px-4 py-4 hidden sm:table-cell">
                            <p class="text-xs text-gray-500">{{ $profile->created_at->format('d/m/Y') }}</p>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('profile.show', $profile) }}" target="_blank"
                                   class="text-xs font-semibold text-gray-500 hover:text-[#0066CC] transition">
                                    Voir
                                </a>
                                @if (! $profile->is_verified)
                                    <button type="button"
                                            wire:click="approve({{ $profile->id }})"
                                            wire:confirm="Approuver le profil de {{ $profile->full_name }} ?"
                                            class="text-xs font-semibold px-3 py-1.5 bg-green-600 text-white hover:bg-green-700 transition">
                                        Approuver
                                    </button>
                                @else
                                    <button type="button"
                                            wire:click="openRejectModal({{ $profile->id }})"
                                            class="text-xs font-semibold px-3 py-1.5 border border-[#DC143C] text-[#DC143C] hover:bg-[#DC143C] hover:text-white transition">
                                        Rejeter
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-400">
                            Aucun profil à modérer.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $profiles->links() }}
    </div>

    {{-- Reject modal --}}
    @if ($rejectingProfile)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             wire:click.self="closeRejectModal">
            <div class="bg-white max-w-md w-full border border-gray-200 shadow-2xl">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-[#111827]">Rejeter ce profil ?</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ $rejectingProfile->full_name }}</p>
                </div>
                <div class="p-6">
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                        Raison du rejet <span class="text-gray-400 normal-case font-normal">(optionnel)</span>
                    </label>
                    <textarea wire:model="rejectionReason" rows="4"
                              placeholder="Expliquez pourquoi ce profil est rejeté — sera envoyé par email au membre."
                              class="w-full border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC] resize-none"></textarea>
                </div>
                <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" wire:click="closeRejectModal"
                            class="text-sm font-semibold px-4 py-2 text-gray-600 hover:text-[#111827] transition">
                        Annuler
                    </button>
                    <button type="button" wire:click="confirmReject"
                            class="text-sm font-semibold px-4 py-2 bg-[#DC143C] text-white hover:bg-red-800 transition">
                        Confirmer le rejet
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
