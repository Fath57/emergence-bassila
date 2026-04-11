@extends('layouts.app')
@section('title', 'Blog')
@section('content')

{{-- Page header --}}
<div class="bg-[#0A1628] relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none"
         style="background-image: linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                                  linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
                background-size: 48px 48px;"></div>
    <div class="absolute bottom-0 left-0 right-0 h-px bg-[#DC143C] opacity-60"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 relative">
        <h1 class="text-2xl font-bold text-white mb-1">Blog</h1>
        <p class="text-sm text-white/40">Articles et actualités de la communauté Bassila Émergence</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Featured article --}}
    @if ($featured)
        <div class="border border-gray-200 overflow-hidden mb-8 group">
            <div class="md:flex">
                @if ($featured->featured_image_url)
                    <div class="md:w-5/12 shrink-0">
                        <img src="{{ $featured->featured_image_url }}"
                             alt="{{ $featured->title }}"
                             class="w-full h-56 md:h-full object-cover">
                    </div>
                @endif
                <div class="p-8 flex flex-col justify-center">
                    <div class="flex items-center gap-3 mb-3">
                        @if ($featured->category)
                            <span class="text-xs font-semibold text-[#0066CC] uppercase tracking-wider">{{ $featured->category->name }}</span>
                        @endif
                        <span class="text-xs text-gray-400 border border-gray-200 px-2 py-0.5">Article à la une</span>
                    </div>
                    <h2 class="text-xl font-bold text-[#111827] mb-3 leading-snug group-hover:text-[#0066CC] transition"
>
                        <a href="{{ route('blog.show', $featured->slug) }}">{{ $featured->title }}</a>
                    </h2>
                    @if ($featured->excerpt)
                        <p class="text-gray-600 text-sm mb-5 leading-relaxed line-clamp-3">{{ $featured->excerpt }}</p>
                    @endif
                    <div class="flex items-center gap-3 text-xs text-gray-400">
                        @if ($featured->user->profile)
                            <span>{{ $featured->user->profile->full_name }}</span>
                            <span>·</span>
                        @endif
                        <span>{{ $featured->published_at->format('d M Y') }}</span>
                        <span>·</span>
                        <span>{{ $featured->reading_time }} min de lecture</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Articles grid --}}
        <div class="flex-1">
            @auth
                <div class="mb-5 flex justify-end">
                    <a href="{{ route('blog.create') }}"
                       class="inline-flex items-center gap-2 bg-[#0066CC] hover:bg-blue-800 text-white text-sm font-semibold px-4 py-2 transition"
                       wire:navigate>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Rédiger un article
                    </a>
                </div>
            @endauth

            @if ($posts->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    @foreach ($posts as $post)
                        @if (! $loop->first || $posts->currentPage() > 1)
                            <article class="border border-gray-200 overflow-hidden group hover:border-[#0066CC] transition">
                                @if ($post->featured_image_url)
                                    <a href="{{ route('blog.show', $post->slug) }}" wire:navigate>
                                        <img src="{{ $post->featured_image_url }}"
                                             alt="{{ $post->title }}"
                                             class="w-full h-40 object-cover">
                                    </a>
                                @endif
                                <div class="p-5">
                                    @if ($post->category)
                                        <span class="text-xs font-semibold text-[#0066CC] uppercase tracking-wider">{{ $post->category->name }}</span>
                                    @endif
                                    <h3 class="font-bold text-[#111827] mt-1.5 mb-1.5 leading-snug group-hover:text-[#0066CC] transition"
>
                                        <a href="{{ route('blog.show', $post->slug) }}" wire:navigate>
                                            {{ $post->title }}
                                        </a>
                                    </h3>
                                    @if ($post->excerpt)
                                        <p class="text-sm text-gray-500 line-clamp-2 mb-3">{{ $post->excerpt }}</p>
                                    @endif
                                    <div class="flex items-center gap-2 text-xs text-gray-400 border-t border-gray-100 pt-3 mt-3">
                                        @if ($post->user->profile)
                                            <span>{{ $post->user->profile->full_name }}</span>
                                            <span>·</span>
                                        @endif
                                        <span>{{ $post->reading_time }} min</span>
                                        <span>·</span>
                                        <span>{{ $post->published_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </article>
                        @endif
                    @endforeach
                </div>
                <div class="mt-8">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="border border-gray-200 p-16 text-center">
                    <p class="text-gray-500 text-sm">Aucun article publié pour l'instant.</p>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        @if ($categories->count() > 0)
            <aside class="lg:w-52 shrink-0">
                <div class="border border-gray-200 p-5 bg-white">
                    <h2 class="font-bold text-sm text-[#111827] mb-4 uppercase tracking-wider">Catégories</h2>
                    <ul class="space-y-2">
                        @foreach ($categories as $category)
                            <li class="flex items-center justify-between text-sm">
                                <span class="text-gray-700">{{ $category->name }}</span>
                                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5">{{ $category->posts_count }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        @endif
    </div>
</div>

@endsection
