<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Demandes de suppression</h1>

    @if (session('status'))
        <p class="text-sm bg-green-50 border border-green-200 text-green-800 p-3 mb-4">{{ session('status') }}</p>
    @endif

    <div class="mb-4">
        <select wire:model.live="statusFilter" class="border border-gray-300 px-3 py-2 text-sm">
            <option value="all">Tous</option>
            <option value="requested">En attente de confirmation</option>
            <option value="confirmed">Confirmées (période de grâce)</option>
            <option value="cancelled">Annulées</option>
            <option value="purged">Purgées</option>
        </select>
    </div>

    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
            <tr>
                <th class="text-left p-3">Utilisateur</th>
                <th class="text-left p-3">Statut</th>
                <th class="text-left p-3">Demandée le</th>
                <th class="text-left p-3">Purge prévue</th>
                <th class="text-left p-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($requests as $r)
                <tr class="border-b">
                    <td class="p-3">
                        {{ $r->user?->first_name }} {{ $r->user?->last_name }}
                        <br><span class="text-xs text-gray-500">{{ $r->user?->email ?? '[compte purgé]' }}</span>
                    </td>
                    <td class="p-3">{{ $r->status }}</td>
                    <td class="p-3">{{ $r->requested_at?->isoFormat('D MMM YYYY') }}</td>
                    <td class="p-3">{{ $r->scheduled_purge_at?->isoFormat('D MMM YYYY') ?? '—' }}</td>
                    <td class="p-3">
                        @if (in_array($r->status, ['requested', 'confirmed']))
                            <input type="text" wire:model="cancelReason.{{ $r->id }}"
                                   placeholder="Raison (optionnelle)"
                                   class="border border-gray-300 px-2 py-1 text-xs mb-1 w-full">
                            <button wire:click="cancel({{ $r->id }})"
                                    class="text-xs bg-gray-200 px-2 py-1 hover:bg-gray-300">Annuler</button>
                            <button wire:click="forcePurge({{ $r->id }})"
                                    wire:confirm="Purger immédiatement ce compte ? Cette action est irréversible."
                                    class="text-xs bg-red-600 text-white px-2 py-1 hover:bg-red-700">Purger maintenant</button>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-6 text-center text-gray-500">Aucune demande.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $requests->links() }}</div>
</div>
