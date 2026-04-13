<div>
    <header class="mb-8">
        <h1 class="font-serif text-3xl font-semibold text-[#0A1628]">Villages</h1>
        <p class="text-sm text-gray-600 mt-2">
            Gérez la liste des villages de la commune (profils, formulaires).
        </p>
    </header>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 px-4 py-3 mb-6 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white border border-gray-200 p-5 mb-8">
        <h2 class="text-xs uppercase tracking-widest text-gray-500 font-semibold mb-3">
            Nouveau village
        </h2>
        <form wire:submit.prevent="create" class="space-y-4">
            <div class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Nom</label>
                    <input wire:model="newName"
                           type="text"
                           maxlength="150"
                           placeholder="Ex. Bassila"
                           class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC] @error('newName') border-red-400 @enderror">
                    @error('newName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Arrondissement</label>
                    <input wire:model="newArrondissement"
                           type="text"
                           maxlength="100"
                           placeholder="Optionnel"
                           class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC] @error('newArrondissement') border-red-400 @enderror">
                    @error('newArrondissement') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="w-28">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Ordre</label>
                    <input wire:model.number="newSortOrder"
                           type="number"
                           min="0"
                           max="65535"
                           class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC] @error('newSortOrder') border-red-400 @enderror">
                    @error('newSortOrder') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-end pb-0.5">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input wire:model="newIsActive" type="checkbox" class="rounded border-gray-300 text-[#0066CC] focus:ring-[#0066CC]">
                        Actif
                    </label>
                </div>
            </div>
            <div>
                <button type="submit"
                        class="bg-[#0066CC] text-white text-sm font-semibold px-5 py-2 hover:bg-[#0052A3] transition">
                    Créer
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white border border-gray-200">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left text-xs uppercase tracking-widest text-gray-500 font-semibold px-5 py-3">Nom</th>
                    <th class="text-left text-xs uppercase tracking-widest text-gray-500 font-semibold px-5 py-3">Arrondissement</th>
                    <th class="text-center text-xs uppercase tracking-widest text-gray-500 font-semibold px-5 py-3">Ordre</th>
                    <th class="text-center text-xs uppercase tracking-widest text-gray-500 font-semibold px-5 py-3">Actif</th>
                    <th class="text-center text-xs uppercase tracking-widest text-gray-500 font-semibold px-5 py-3">Profils</th>
                    <th class="text-right text-xs uppercase tracking-widest text-gray-500 font-semibold px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($villages as $village)
                    <tr class="border-b border-gray-100 last:border-0">
                        @if ($editingId === $village->id)
                            <td colspan="6" class="px-5 py-4 bg-blue-50/50">
                                <form wire:submit.prevent="saveEdit" class="space-y-4">
                                    <div class="flex flex-wrap gap-4">
                                        <div class="flex-1 min-w-[200px]">
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Nom</label>
                                            <input wire:model="editingName"
                                                   type="text"
                                                   maxlength="150"
                                                   autofocus
                                                   class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC] @error('editingName') border-red-400 @enderror">
                                            @error('editingName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="flex-1 min-w-[200px]">
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Arrondissement</label>
                                            <input wire:model="editingArrondissement"
                                                   type="text"
                                                   maxlength="100"
                                                   class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC] @error('editingArrondissement') border-red-400 @enderror">
                                            @error('editingArrondissement') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="w-28">
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Ordre</label>
                                            <input wire:model.number="editingSortOrder"
                                                   type="number"
                                                   min="0"
                                                   max="65535"
                                                   class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC] @error('editingSortOrder') border-red-400 @enderror">
                                            @error('editingSortOrder') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="flex items-end pb-0.5">
                                            <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                                <input wire:model="editingIsActive" type="checkbox" class="rounded border-gray-300 text-[#0066CC] focus:ring-[#0066CC]">
                                                Actif
                                            </label>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-3">
                                        <button type="submit"
                                                class="bg-[#0066CC] text-white text-sm font-semibold px-4 py-2 hover:bg-[#0052A3] transition">
                                            Enregistrer
                                        </button>
                                        <button type="button"
                                                wire:click="cancelEdit"
                                                class="border border-gray-300 text-sm text-gray-600 px-4 py-2 hover:bg-gray-50 transition">
                                            Annuler
                                        </button>
                                    </div>
                                </form>
                            </td>
                        @else
                            <td class="px-5 py-4 text-sm font-semibold text-[#0A1628]">{{ $village->name }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $village->arrondissement ?? '—' }}</td>
                            <td class="px-5 py-4 text-sm text-center text-gray-700">{{ $village->sort_order }}</td>
                            <td class="px-5 py-4 text-center">
                                @if ($village->is_active)
                                    <span class="inline-block bg-green-50 text-green-800 border border-green-200 px-2 py-0.5 text-xs font-semibold">Oui</span>
                                @else
                                    <span class="inline-block bg-gray-100 text-gray-600 px-2 py-0.5 text-xs font-semibold">Non</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-center">
                                @if ($village->profiles_count > 0)
                                    <span class="inline-block bg-gray-100 text-gray-700 px-2 py-1 text-xs font-semibold">
                                        {{ $village->profiles_count }}
                                    </span>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-3 text-xs font-semibold">
                                    <button type="button"
                                            wire:click="startEdit({{ $village->id }})"
                                            class="text-[#0066CC] hover:underline">
                                        Modifier
                                    </button>
                                    <button type="button"
                                            wire:click="delete({{ $village->id }})"
                                            @if ($village->profiles_count > 0)
                                                wire:confirm="Ce village est lié à {{ $village->profiles_count }} profil(s). La liaison sera retirée. Continuer ?"
                                            @else
                                                wire:confirm="Supprimer ce village ?"
                                            @endif
                                            class="text-[#DC143C] hover:underline">
                                        Supprimer
                                    </button>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center">
                            <p class="text-sm text-gray-500">Aucun village pour l'instant.</p>
                            <p class="text-xs text-gray-400 mt-1">
                                Créez un village ci-dessus ou exécutez le seeder.
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
