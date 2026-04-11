<div>
@section('title', 'Aperçu — ' . $post->title)

    {{-- Amber preview banner --}}
    <div class="bg-amber-50 border-b-2 border-amber-200 px-4 py-3">
        <div class="max-w-3xl mx-auto flex items-center justify-between flex-wrap gap-3">
            <p class="text-sm font-semibold text-amber-800 flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Aperçu — Cet article est {{ $post->status === 'draft' ? 'un brouillon' : $post->status }} et n'est pas visible publiquement.
            </p>
            <a href="{{ route('blog.edit', $post->slug) }}"
               class="text-sm font-semibold text-[#0066CC] hover:underline">
                Retour à l'édition
            </a>
        </div>
    </div>

    <article class="max-w-3xl mx-auto px-5 py-12">
        @if ($post->featured_image_url)
            <img src="{{ $post->featured_image_url }}"
                 alt=""
                 class="w-full aspect-[16/9] object-cover mb-8">
        @endif

        @if ($post->category)
            <p class="text-xs uppercase tracking-widest text-[#0066CC] font-semibold mb-3">
                {{ $post->category->name }}
            </p>
        @endif

        <h1 class="font-serif text-3xl md:text-5xl font-semibold text-[#0A1628] leading-tight">
            {{ $post->title }}
        </h1>

        <div class="mt-6 flex items-center gap-3 text-sm text-gray-600 border-b border-gray-100 pb-6">
            <span class="font-semibold text-[#0A1628]">{{ $post->user->name }}</span>
            <span>·</span>
            <span>{{ $post->reading_time }} min de lecture</span>
        </div>

        <div class="mt-10 prose prose-lg max-w-none prose-headings:font-serif prose-headings:text-[#0A1628] prose-a:text-[#0066CC]">
            {!! $post->content !!}
        </div>
    </article>

    <div class="max-w-3xl mx-auto px-5 pb-12">
        <div class="bg-gray-50 border border-gray-200 p-5 text-center">
            <p class="text-xs text-gray-500">Aperçu — les commentaires sont désactivés sur les aperçus.</p>
        </div>
    </div>
</div>
