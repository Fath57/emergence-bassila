<div>
    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-xs font-semibold text-[#DC143C] uppercase tracking-widest mb-1">Newsletter</p>
            <h1 class="text-3xl font-bold text-[#111827]">Abonnés</h1>
        </div>
        <div class="flex gap-3">
            <button wire:click="$set('showImportModal', true)"
                    class="text-sm px-5 py-2 bg-gray-800 hover:bg-gray-900 text-white font-semibold transition">
                Importer CSV
            </button>
            <a href="{{ route('admin.newsletter') }}" wire:navigate
               class="text-sm px-5 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                ← Campagnes
            </a>
        </div>
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

    {{-- CSV Import modal --}}
    @if ($showImportModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white p-6 max-w-lg w-full">
                @if ($importResult)
                    <h3 class="font-bold text-[#111827] mb-4">Résultats de l'import</h3>
                    <div class="space-y-2 mb-5 text-sm">
                        <p class="text-green-700 font-semibold">✓ {{ $importResult['imported'] }} abonné(s) importé(s)</p>
                        @if ($importResult['skipped'] > 0)
                            <p class="text-gray-600">→ {{ $importResult['skipped'] }} email(s) déjà présent(s), ignoré(s)</p>
                        @endif
                        @foreach ($importResult['errors'] as $err)
                            <p class="text-red-600 text-xs">⚠ {{ $err }}</p>
                        @endforeach
                    </div>
                    <button wire:click="closeImport"
                            class="px-5 py-2 bg-[#0066CC] text-white text-sm font-semibold hover:bg-blue-700">
                        Fermer
                    </button>
                @else
                    <h3 class="font-bold text-[#111827] mb-2">Importer des abonnés (CSV)</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Le fichier doit contenir une colonne <code>email</code> et optionnellement <code>first_name</code>.<br>
                        Les emails déjà présents seront ignorés. Les imports sont marqués comme <strong>confirmés</strong> (consentement RGPD obtenu à la source).
                    </p>

                    <div class="mb-4">
                        <input wire:model="csvFile" type="file" accept=".csv,.txt"
                               class="block w-full text-sm text-gray-600 border border-gray-300 p-2">
                        @error('csvFile') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3">
                        <button wire:click="importCsv"
                                wire:loading.attr="disabled"
                                class="px-5 py-2 bg-gray-800 text-white text-sm font-semibold hover:bg-gray-900 transition">
                            <span wire:loading.remove>Importer</span>
                            <span wire:loading>Import en cours…</span>
                        </button>
                        <button wire:click="closeImport"
                                class="px-5 py-2 border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">
                            Annuler
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
