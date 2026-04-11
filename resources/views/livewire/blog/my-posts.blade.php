<div>
@section('title', 'Mes articles')

    <div class="max-w-5xl mx-auto px-5 py-10">

        {{-- Header --}}
        <div class="flex items-start justify-between flex-wrap gap-4 mb-8">
            <div>
                <h1 class="font-serif text-3xl md:text-4xl font-semibold text-[#0A1628]">Mes articles</h1>
                <p class="text-sm text-gray-600 mt-2">Gérez vos articles, brouillons et archives.</p>
            </div>
            <a href="{{ route('blog.create') }}"
               wire:navigate
               class="inline-flex items-center gap-2 bg-[#0066CC] text-white text-sm font-semibold px-5 py-2.5 hover:bg-[#0052A3] transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Rédiger un nouvel article
            </a>
        </div>

        {{-- Tabs --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex gap-6 -mb-px" role="tablist">
                @foreach ([
                    'all'       => 'Tous',
                    'draft'     => 'Brouillons',
                    'published' => 'Publiés',
                    'archived'  => 'Archivés',
                ] as $key => $label)
                    <button type="button"
                            wire:click="$set('filter', '{{ $key }}')"
                            class="py-3 text-sm font-semibold transition border-b-2 {{ $filter === $key ? 'border-[#0066CC] text-[#0066CC]' : 'border-transparent text-gray-500 hover:text-gray-900' }}">
                        {{ $label }} <span class="text-xs text-gray-400">({{ $counts[$key] }})</span>
                    </button>
                @endforeach
            </nav>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 px-4 py-3 mb-6">
                <p class="text-sm text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Posts list --}}
        <div class="space-y-4">
            @forelse ($posts as $post)
                <article class="bg-white border border-gray-200 p-5 flex gap-5 items-start">
                    @if ($post->featured_image_url)
                        <img src="{{ $post->featured_image_url }}"
                             alt=""
                             class="w-28 h-20 object-cover flex-shrink-0">
                    @else
                        <div class="w-28 h-20 bg-gray-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                            </svg>
                        </div>
                    @endif

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="font-serif text-lg font-semibold text-[#0A1628] truncate">{{ $post->title }}</h2>
                                @if ($post->excerpt)
                                    <p class="text-sm text-gray-600 mt-1 line-clamp-1">{{ $post->excerpt }}</p>
                                @endif
                            </div>

                            @php
                                $statusLabels = [
                                    'draft'     => ['label' => 'Brouillon', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                                    'published' => ['label' => 'Publié',    'class' => 'bg-green-50 text-green-700 border-green-200'],
                                    'archived'  => ['label' => 'Archivé',   'class' => 'bg-gray-50  text-gray-600  border-gray-200'],
                                ];
                                $s = $statusLabels[$post->status] ?? $statusLabels['draft'];
                            @endphp
                            <span class="inline-block text-[10px] uppercase tracking-widest font-semibold px-2 py-1 border {{ $s['class'] }} whitespace-nowrap">
                                {{ $s['label'] }}
                            </span>
                        </div>

                        <div class="mt-3 flex items-center justify-between flex-wrap gap-3">
                            <p class="text-xs text-gray-500">
                                @if ($post->status === 'published' && $post->published_at)
                                    Publié le {{ $post->published_at->format('d/m/Y') }}
                                @else
                                    Modifié {{ $post->updated_at->diffForHumans() }}
                                @endif
                                · {{ $post->reading_time }} min de lecture
                            </p>

                            <div class="flex items-center gap-3 text-xs font-semibold">
                                @if ($post->status === 'published')
                                    <a href="{{ route('blog.show', $post->slug) }}"
                                       class="text-[#0066CC] hover:underline">
                                        Voir
                                    </a>
                                @else
                                    <a href="{{ route('blog.preview', $post) }}"
                                       target="_blank"
                                       class="text-[#0066CC] hover:underline">
                                        Aperçu
                                    </a>
                                @endif
                                <a href="{{ route('blog.edit', $post->slug) }}"
                                   wire:navigate
                                   class="text-[#0066CC] hover:underline">
                                    Modifier
                                </a>
                                <button type="button"
                                        wire:click="delete({{ $post->id }})"
                                        wire:confirm="Supprimer cet article ? Cette action est irréversible."
                                        class="text-[#DC143C] hover:underline">
                                    Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="bg-white border border-gray-200 p-10 text-center">
                    <p class="text-sm text-gray-600">Aucun article pour l'instant.</p>
                    <a href="{{ route('blog.create') }}"
                       wire:navigate
                       class="inline-block mt-4 text-sm font-semibold text-[#0066CC] hover:underline">
                        Rédiger votre premier article →
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($posts->hasPages())
            <div class="mt-8">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
