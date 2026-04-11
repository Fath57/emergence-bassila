<div>
    <header class="mb-8">
        <h1 class="font-serif text-3xl font-semibold text-[#0A1628]">Catégories</h1>
        <p class="text-sm text-gray-600 mt-2">
            Gérez les catégories disponibles pour les articles du blog.
        </p>
    </header>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 px-4 py-3 mb-6 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Create form --}}
    <div class="bg-white border border-gray-200 p-5 mb-8">
        <h2 class="text-xs uppercase tracking-widest text-gray-500 font-semibold mb-3">
            Nouvelle catégorie
        </h2>
        <form wire:submit.prevent="create" class="flex flex-wrap items-start gap-3">
            <div class="flex-1 min-w-[240px]">
                <input wire:model="newName"
                       type="text"
                       maxlength="60"
                       placeholder="Nom de la catégorie"
                       class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC] @error('newName') border-red-400 @enderror">
                @error('newName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <button type="submit"
                    class="bg-[#0066CC] text-white text-sm font-semibold px-5 py-2 hover:bg-[#0052A3] transition">
                Créer
            </button>
        </form>
    </div>

    {{-- Categories table --}}
    <div class="bg-white border border-gray-200">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left text-xs uppercase tracking-widest text-gray-500 font-semibold px-5 py-3">Nom</th>
                    <th class="text-left text-xs uppercase tracking-widest text-gray-500 font-semibold px-5 py-3">Slug</th>
                    <th class="text-center text-xs uppercase tracking-widest text-gray-500 font-semibold px-5 py-3">Articles</th>
                    <th class="text-right text-xs uppercase tracking-widest text-gray-500 font-semibold px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr class="border-b border-gray-100 last:border-0">
                        @if ($editingId === $category->id)
                            {{-- Inline edit row --}}
                            <td colspan="4" class="px-5 py-4 bg-blue-50/50">
                                <form wire:submit.prevent="saveEdit" class="flex flex-wrap items-start gap-3">
                                    <div class="flex-1 min-w-[240px]">
                                        <input wire:model="editingName"
                                               type="text"
                                               maxlength="60"
                                               autofocus
                                               class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC] @error('editingName') border-red-400 @enderror">
                                        @error('editingName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                    <button type="submit"
                                            class="bg-[#0066CC] text-white text-sm font-semibold px-4 py-2 hover:bg-[#0052A3] transition">
                                        Enregistrer
                                    </button>
                                    <button type="button"
                                            wire:click="cancelEdit"
                                            class="border border-gray-300 text-sm text-gray-600 px-4 py-2 hover:bg-gray-50 transition">
                                        Annuler
                                    </button>
                                </form>
                            </td>
                        @else
                            <td class="px-5 py-4 text-sm font-semibold text-[#0A1628]">{{ $category->name }}</td>
                            <td class="px-5 py-4 text-sm text-gray-500 font-mono">{{ $category->slug }}</td>
                            <td class="px-5 py-4 text-sm text-center">
                                @if ($category->posts_count > 0)
                                    <span class="inline-block bg-gray-100 text-gray-700 px-2 py-1 text-xs font-semibold">
                                        {{ $category->posts_count }}
                                    </span>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-3 text-xs font-semibold">
                                    <button type="button"
                                            wire:click="startEdit({{ $category->id }})"
                                            class="text-[#0066CC] hover:underline">
                                        Renommer
                                    </button>
                                    <button type="button"
                                            wire:click="delete({{ $category->id }})"
                                            @if ($category->posts_count > 0)
                                                wire:confirm="Cette catégorie contient {{ $category->posts_count }} article(s). Ils deviendront sans catégorie. Continuer ?"
                                            @else
                                                wire:confirm="Supprimer cette catégorie ?"
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
                        <td colspan="4" class="px-5 py-10 text-center">
                            <p class="text-sm text-gray-500">Aucune catégorie pour l'instant.</p>
                            <p class="text-xs text-gray-400 mt-1">
                                Créez votre première catégorie ci-dessus.
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
