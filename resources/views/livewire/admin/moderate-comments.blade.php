<div>
    <div class="mb-8">
        <p class="text-xs font-semibold text-[#DC143C] uppercase tracking-widest mb-2">Modération</p>
        <h1 class="text-3xl font-bold text-[#111827]">Commentaires</h1>
        <p class="text-gray-500 mt-1">Approuvez les commentaires avant qu'ils ne soient visibles sur le blog.</p>
    </div>

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 p-4 mb-6 flex justify-end">
        <div class="flex gap-1 bg-gray-50 p-1 border border-gray-200">
            @foreach (['pending' => 'En attente', 'approved' => 'Approuvés', 'all' => 'Tous'] as $value => $label)
                <button type="button"
                        wire:click="$set('filter', '{{ $value }}')"
                        class="text-xs font-semibold px-3 py-1.5 transition {{ $filter === $value ? 'bg-[#0066CC] text-white' : 'text-gray-600 hover:text-[#111827]' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Comments list --}}
    <div class="space-y-3">
        @forelse ($comments as $comment)
            <div class="bg-white border border-gray-200 p-5">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="font-semibold text-[#111827] text-sm">{{ $comment->display_author_name }}</p>
                            @if ($comment->moderated_at)
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-700 bg-green-50 border border-green-100 px-2 py-0.5">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Approuvé
                                </span>
                            @else
                                <span class="inline-flex text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-100 px-2 py-0.5">
                                    En attente
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400">
                            Sur l'article <a href="{{ route('blog.show', $comment->post->slug) }}" target="_blank" class="text-[#0066CC] hover:underline">{{ $comment->post->title }}</a>
                            · {{ $comment->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
                <div class="bg-gray-50 border border-gray-100 p-4 mb-3">
                    <p class="text-sm text-[#333333] leading-relaxed whitespace-pre-line">{{ $comment->content }}</p>
                </div>
                <div class="flex items-center justify-end gap-2">
                    @if (! $comment->moderated_at)
                        <button type="button"
                                wire:click="approve({{ $comment->id }})"
                                class="text-xs font-semibold px-3 py-1.5 bg-green-600 text-white hover:bg-green-700 transition">
                            Approuver
                        </button>
                    @endif
                    <button type="button"
                            wire:click="delete({{ $comment->id }})"
                            wire:confirm="Supprimer ce commentaire ?"
                            class="text-xs font-semibold text-gray-500 hover:text-[#DC143C] transition">
                        Supprimer
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-white border border-gray-200 p-12 text-center text-sm text-gray-400">
                Aucun commentaire trouvé.
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $comments->links() }}
    </div>
</div>
