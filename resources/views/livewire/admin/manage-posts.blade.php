<div>
    <div class="mb-8">
        <p class="text-xs font-semibold text-[#DC143C] uppercase tracking-widest mb-2">Administration</p>
        <h1 class="text-3xl font-bold text-[#111827]">Articles</h1>
        <p class="text-gray-500 mt-1">Publiez, dépubliez ou supprimez les articles du blog.</p>
    </div>

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 p-4 mb-6 flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>
            </svg>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Rechercher un article…"
                   class="w-full border border-gray-200 pl-10 pr-4 py-2 text-sm focus:outline-none focus:border-[#0066CC]">
        </div>
        <div class="flex gap-1 bg-gray-50 p-1 border border-gray-200">
            @foreach (['all' => 'Tous', 'draft' => 'Brouillons', 'published' => 'Publiés', 'archived' => 'Archivés'] as $value => $label)
                <button type="button"
                        wire:click="$set('status', '{{ $value }}')"
                        class="text-xs font-semibold px-3 py-1.5 transition {{ $status === $value ? 'bg-[#0066CC] text-white' : 'text-gray-600 hover:text-[#111827]' }}">
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
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Titre</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Auteur</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Catégorie</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($posts as $post)
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-4 max-w-xs">
                            <p class="font-semibold text-[#111827] truncate">{{ $post->title }}</p>
                            <p class="text-xs text-gray-400">{{ $post->created_at->format('d/m/Y') }}</p>
                        </td>
                        <td class="px-4 py-4 hidden md:table-cell">
                            <p class="text-sm text-gray-600">{{ $post->user->name }}</p>
                        </td>
                        <td class="px-4 py-4 hidden lg:table-cell">
                            <p class="text-xs font-semibold text-[#0066CC] uppercase tracking-wider">
                                {{ $post->category?->name ?? '—' }}
                            </p>
                        </td>
                        <td class="px-4 py-4">
                            @php
                                $statusConfig = [
                                    'draft'     => ['Brouillon', 'bg-gray-100 text-gray-700'],
                                    'published' => ['Publié', 'bg-green-50 text-green-700 border-green-200'],
                                    'archived'  => ['Archivé', 'bg-gray-100 text-gray-500'],
                                ][$post->status] ?? [$post->status, 'bg-gray-100 text-gray-600'];
                            @endphp
                            <span class="inline-flex text-xs font-semibold px-2 py-1 border {{ $statusConfig[1] }}">
                                {{ $statusConfig[0] }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if ($post->status !== 'published')
                                    <button type="button"
                                            wire:click="publish({{ $post->id }})"
                                            wire:confirm="Publier « {{ $post->title }} » ?"
                                            class="text-xs font-semibold px-3 py-1.5 bg-green-600 text-white hover:bg-green-700 transition">
                                        Publier
                                    </button>
                                @else
                                    <button type="button"
                                            wire:click="unpublish({{ $post->id }})"
                                            wire:confirm="Dépublier « {{ $post->title }} » ?"
                                            class="text-xs font-semibold px-3 py-1.5 border border-amber-500 text-amber-700 hover:bg-amber-500 hover:text-white transition">
                                        Dépublier
                                    </button>
                                @endif
                                <button type="button"
                                        wire:click="delete({{ $post->id }})"
                                        wire:confirm="Supprimer définitivement « {{ $post->title }} » ?"
                                        class="text-xs font-semibold text-gray-500 hover:text-[#DC143C] transition">
                                    Supprimer
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-400">
                            Aucun article trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $posts->links() }}
    </div>
</div>
