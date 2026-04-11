<div>
    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-xs font-semibold text-[#DC143C] uppercase tracking-widest mb-1">Newsletter</p>
            <h1 class="text-3xl font-bold text-[#111827]">Abonnés</h1>
        </div>
        <a href="{{ route('admin.newsletter') }}" wire:navigate
           class="text-sm px-5 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
            ← Campagnes
        </a>
    </div>

    {{-- Filter tabs + Search --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
        <div class="flex gap-1">
            @foreach (['all' => 'Tous', 'active' => 'Confirmés', 'pending' => 'En attente', 'unsubscribed' => 'Désabonnés'] as $key => $label)
                <button wire:click="$set('filter', '{{ $key }}')"
                        class="text-sm px-3 py-1.5 font-semibold transition {{ $filter === $key ? 'bg-[#0066CC] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    {{ $label }}
                    <span class="ml-1 text-xs {{ $filter === $key ? 'text-blue-100' : 'text-gray-400' }}">({{ $counts[$key] }})</span>
                </button>
            @endforeach
        </div>
        <input wire:model.live.debounce.300ms="search"
               type="text"
               placeholder="Rechercher par email…"
               class="text-sm px-4 py-2 border border-gray-300 w-56 focus:outline-none focus:border-[#0066CC]">
    </div>

    @if ($subscribers->isEmpty())
        <div class="bg-white border border-gray-200 p-10 text-center text-gray-500">
            Aucun abonné trouvé.
        </div>
    @else
        <div class="bg-white border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Prénom</th>
                        <th class="px-4 py-3 text-left">Source</th>
                        <th class="px-4 py-3 text-left">Statut</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($subscribers as $sub)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 font-medium text-[#111827]">{{ $sub->email }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $sub->first_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $sub->source }}</td>
                            <td class="px-4 py-3">
                                @if ($sub->unsubscribed_at)
                                    <span class="text-xs font-semibold bg-red-100 text-red-700 px-2 py-0.5">Désabonné</span>
                                @elseif ($sub->confirmed_at)
                                    <span class="text-xs font-semibold bg-green-100 text-green-700 px-2 py-0.5">Confirmé</span>
                                @else
                                    <span class="text-xs font-semibold bg-amber-100 text-amber-700 px-2 py-0.5">En attente</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $sub->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    @if ($sub->unsubscribed_at)
                                        <button wire:click="reSubscribe({{ $sub->id }})"
                                                class="text-xs text-emerald-600 hover:underline font-semibold">
                                            Réinscrire
                                        </button>
                                    @elseif ($sub->confirmed_at)
                                        <button wire:click="unsubscribe({{ $sub->id }})"
                                                class="text-xs text-amber-600 hover:underline font-semibold">
                                            Désabonner
                                        </button>
                                    @endif
                                    <button wire:click="delete({{ $sub->id }})"
                                            wire:confirm="Supprimer définitivement cet abonné ?"
                                            class="text-xs text-red-500 hover:underline font-semibold">
                                        Supprimer
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $subscribers->links() }}
        </div>
    @endif
</div>
