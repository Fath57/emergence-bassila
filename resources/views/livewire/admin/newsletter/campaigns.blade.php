<div>
    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-xs font-semibold text-[#DC143C] uppercase tracking-widest mb-1">Newsletter</p>
            <h1 class="text-3xl font-bold text-[#111827]">Campagnes</h1>
        </div>
        <a href="{{ route('admin.newsletter.create') }}" wire:navigate
           class="bg-[#0066CC] hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 transition">
            + Nouvelle campagne
        </a>
    </div>

    @if ($campaigns->isEmpty())
        <div class="bg-white border border-gray-200 p-10 text-center text-gray-500">
            Aucune campagne. <a href="{{ route('admin.newsletter.create') }}" wire:navigate class="text-[#0066CC] underline">Créer la première</a>.
        </div>
    @else
        <div class="bg-white border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Sujet</th>
                        <th class="px-4 py-3 text-left">Statut</th>
                        <th class="px-4 py-3 text-right">Dest.</th>
                        <th class="px-4 py-3 text-right">Envoyés</th>
                        <th class="px-4 py-3 text-right">Ouvertures</th>
                        <th class="px-4 py-3 text-left">Date d'envoi</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($campaigns as $campaign)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 font-medium text-[#111827]">
                                {{ $campaign->subject }}
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $badge = match($campaign->status) {
                                        'draft'   => 'bg-gray-100 text-gray-700',
                                        'sending' => 'bg-blue-100 text-blue-700',
                                        'sent'    => 'bg-green-100 text-green-700',
                                        'failed'  => 'bg-red-100 text-red-700',
                                        default   => 'bg-gray-100 text-gray-600',
                                    };
                                    $label = match($campaign->status) {
                                        'draft'   => 'Brouillon',
                                        'sending' => 'En cours',
                                        'sent'    => 'Envoyée',
                                        'failed'  => 'Échouée',
                                        default   => $campaign->status,
                                    };
                                @endphp
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 {{ $badge }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ $campaign->recipients_count ?: '—' }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">{{ $campaign->sent_count ?: '—' }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">
                                @if ($campaign->sent_count > 0)
                                    {{ $campaign->opens_count }} ({{ $campaign->openRate() }} %)
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $campaign->sent_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($campaign->isEditable())
                                        <a href="{{ route('admin.newsletter.edit', $campaign) }}" wire:navigate
                                           class="text-[#0066CC] hover:underline text-xs font-semibold">
                                            Modifier
                                        </a>
                                        <button wire:click="$set('confirmLaunchId', {{ $campaign->id }})"
                                                class="text-emerald-600 hover:underline text-xs font-semibold">
                                            Lancer
                                        </button>
                                        <button wire:click="$set('confirmDeleteId', {{ $campaign->id }})"
                                                class="text-red-500 hover:underline text-xs font-semibold">
                                            Supprimer
                                        </button>
                                    @else
                                        <a href="{{ route('admin.newsletter.preview', $campaign) }}" wire:navigate
                                           class="text-gray-500 hover:underline text-xs">
                                            Aperçu
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $campaigns->links() }}
        </div>
    @endif

    {{-- Launch confirmation modal --}}
    @if ($confirmLaunchId)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white p-6 max-w-sm w-full">
                <h3 class="font-bold text-[#111827] mb-3">Lancer l'envoi ?</h3>
                <p class="text-sm text-gray-600 mb-5">
                    Cette action enverra la campagne à tous les abonnés confirmés. Elle ne peut pas être annulée.
                </p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="$set('confirmLaunchId', null)"
                            class="text-sm px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button wire:click="launch({{ $confirmLaunchId }})"
                            class="text-sm px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
                        Confirmer l'envoi
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete confirmation modal --}}
    @if ($confirmDeleteId)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white p-6 max-w-sm w-full">
                <h3 class="font-bold text-[#111827] mb-3">Supprimer cette campagne ?</h3>
                <p class="text-sm text-gray-600 mb-5">Cette action est irréversible.</p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="$set('confirmDeleteId', null)"
                            class="text-sm px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button wire:click="delete({{ $confirmDeleteId }})"
                            class="text-sm px-4 py-2 bg-[#DC143C] hover:bg-red-700 text-white font-semibold">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
