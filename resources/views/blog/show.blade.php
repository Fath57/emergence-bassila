@extends('layouts.app')
@section('title', $post->title)
@section('content')

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Breadcrumb --}}
    <nav class="mb-6 text-sm text-gray-400 flex items-center gap-2">
        <a href="{{ route('blog.index') }}" class="hover:text-[#0066CC] transition" wire:navigate>Blog</a>
        <span>/</span>
        <span class="text-gray-700 truncate">{{ $post->title }}</span>
    </nav>

    {{-- Article --}}
    <article class="bg-white border border-gray-200 overflow-hidden mb-10">

        @if ($post->featured_image_url)
            <img src="{{ $post->featured_image_url }}"
                 alt="{{ $post->title }}"
                 class="w-full h-64 object-cover">
        @endif

        <div class="p-8 sm:p-10">

            {{-- Meta --}}
            <div class="flex flex-wrap items-center gap-3 mb-4">
                @if ($post->category)
                    <span class="text-xs font-semibold text-[#0066CC] uppercase tracking-wider">{{ $post->category->name }}</span>
                    <span class="text-gray-200">|</span>
                @endif
                <span class="text-xs text-gray-400">{{ $post->reading_time }} min de lecture</span>
                <span class="text-gray-200">|</span>
                <span class="text-xs text-gray-400">{{ $post->published_at->format('d M Y') }}</span>
            </div>

            {{-- Title --}}
            <h1 class="text-2xl font-bold text-[#111827] mb-6 leading-tight"
                style="font-family: 'Lora', serif;">
                {{ $post->title }}
            </h1>

            {{-- Author --}}
            @if ($post->user->profile)
                <div class="flex items-center gap-3 mb-8 pb-6 border-b border-gray-100">
                    @if ($post->user->profile->avatar_url)
                        <img src="{{ $post->user->profile->avatar_url }}"
                             alt="{{ $post->user->profile->full_name }}"
                             class="h-10 w-10 object-cover">
                    @else
                        <div class="h-10 w-10 bg-[#0066CC] flex items-center justify-center text-white text-sm font-bold shrink-0">
                            {{ strtoupper(substr($post->user->profile->full_name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <a href="{{ route('profile.show', $post->user->profile) }}"
                           class="text-sm font-semibold text-[#111827] hover:text-[#0066CC] transition"
                           wire:navigate>
                            {{ $post->user->profile->full_name }}
                        </a>
                        @if($post->user->profile->job_title)
                            <p class="text-xs text-gray-500">{{ $post->user->profile->job_title }}</p>
                        @endif
                    </div>
                    @can('update', $post)
                        <div class="ml-auto">
                            <a href="{{ route('blog.edit', $post->slug) }}"
                               class="text-sm text-gray-500 hover:text-[#0066CC] border border-gray-200 px-3 py-1 hover:border-[#0066CC] transition"
                               wire:navigate>
                                Modifier
                            </a>
                        </div>
                    @endcan
                </div>
            @endif

            {{-- Content --}}
            <div class="text-gray-700 text-base leading-relaxed whitespace-pre-wrap">
                {!! nl2br(e($post->content)) !!}
            </div>
        </div>
    </article>

    {{-- Comments --}}
    <section>
        <h2 class="font-bold text-lg text-[#111827] mb-5 pb-3 border-b border-gray-200"
            style="font-family: 'Lora', serif;">
            Commentaires <span class="font-normal text-gray-400 text-base">({{ $post->comments->count() }})</span>
        </h2>

        @if ($post->comments->count() > 0)
            <div class="space-y-4 mb-6">
                @foreach ($post->comments as $comment)
                    <div class="bg-white border border-gray-200 p-5">
                        <div class="flex items-center gap-3 mb-3">
                            @if ($comment->user->profile?->avatar_url)
                                <img src="{{ $comment->user->profile->avatar_url }}"
                                     alt=""
                                     class="h-8 w-8 object-cover">
                            @else
                                <div class="h-8 w-8 bg-gray-200 flex items-center justify-center text-gray-600 text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <span class="text-sm font-semibold text-[#111827]">
                                    {{ $comment->user->profile?->full_name ?? $comment->user->name }}
                                </span>
                                <span class="text-xs text-gray-400 ml-2">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $comment->content }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500 mb-6">Soyez le premier à commenter cet article.</p>
        @endif

        @auth
            <livewire:blog.comment-form :post="$post" />
        @else
            <div class="border border-gray-200 p-6 text-center">
                <p class="text-sm text-gray-600 mb-4">Connectez-vous pour laisser un commentaire.</p>
                <a href="{{ route('login') }}"
                   class="bg-[#0066CC] hover:bg-blue-800 text-white text-sm font-semibold px-5 py-2 transition"
                   wire:navigate>
                    Se connecter
                </a>
            </div>
        @endauth
    </section>
</div>

@endsection
