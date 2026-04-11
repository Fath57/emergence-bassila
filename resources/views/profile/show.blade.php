@extends('layouts.app')
@section('title', $profile->full_name)
@section('content')

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Profile header --}}
    <div class="bg-white border border-gray-200 mb-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 bottom-0 w-1 bg-[#DC143C]"></div>
        <div class="p-8 pl-10">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">

            {{-- Avatar --}}
            <div class="shrink-0">
                @if ($profile->avatar_url)
                    <img src="{{ $profile->avatar_url }}"
                         alt="{{ $profile->full_name }}"
                         class="h-24 w-24 object-cover border border-gray-200">
                @else
                    <div class="h-24 w-24 bg-[#0066CC] flex items-center justify-center text-white text-2xl font-bold">
                        {{ strtoupper(substr($profile->full_name, 0, 1)) }}
                    </div>
                @endif
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <h1 class="text-xl font-bold text-[#111827]">
                        {{ $profile->full_name }}
                    </h1>
                    @if ($profile->is_verified)
                        <span class="inline-flex items-center gap-1 bg-blue-50 text-[#0066CC] text-xs font-semibold px-2 py-0.5 border border-blue-200">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Vérifié
                        </span>
                    @endif
                </div>

                <p class="text-gray-600 text-sm">
                    {{ $profile->job_title }}
                    @if ($profile->company)
                        <span class="text-gray-400"> · </span>{{ $profile->company }}
                    @endif
                </p>

                <p class="text-sm text-gray-500 mt-1">
                    {{ $profile->city ? $profile->city . ', ' : '' }}{{ $profile->country }}
                    @if ($profile->sector)
                        <span class="text-gray-300 mx-1">·</span>{{ $profile->sector->name }}
                    @endif
                </p>

                <div class="flex items-center gap-4 mt-3">
                    @if ($profile->linkedin_url)
                        <a href="{{ $profile->linkedin_url }}"
                           target="_blank" rel="noopener noreferrer"
                           class="text-xs text-[#0066CC] font-semibold hover:underline uppercase tracking-wider">
                            LinkedIn
                        </a>
                    @endif
                    @if ($profile->portfolio_url)
                        <a href="{{ $profile->portfolio_url }}"
                           target="_blank" rel="noopener noreferrer"
                           class="text-xs text-[#0066CC] font-semibold hover:underline uppercase tracking-wider">
                            Portfolio
                        </a>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="shrink-0">
                @auth
                    @if (auth()->id() === $profile->user_id)
                        <a href="{{ route('profile.edit') }}"
                           class="border border-gray-200 hover:border-gray-400 text-gray-700 text-sm font-medium px-4 py-2 transition"
                           wire:navigate>
                            Modifier
                        </a>
                    @else
                        <button
                            onclick="document.getElementById('contact-form').scrollIntoView({behavior:'smooth'})"
                            class="bg-[#0066CC] hover:bg-blue-800 text-white text-sm font-semibold px-5 py-2 transition">
                            Contacter
                        </button>
                    @endif
                @else
                    <a href="{{ route('login') }}"
                       class="bg-[#0066CC] hover:bg-blue-800 text-white text-sm font-semibold px-5 py-2 transition">
                        Contacter
                    </a>
                @endauth
            </div>
        </div>

        @if ($profile->bio)
            <div class="mt-6 pt-6 border-t border-gray-100">
                <p class="text-sm text-gray-600 leading-relaxed">{{ $profile->bio }}</p>
            </div>
        @endif
        </div>{{-- /p-8 pl-10 --}}
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Skills --}}
        @if ($profile->skills->count() > 0)
            <div class="bg-white border border-gray-200 p-6">
                <h2 class="font-bold text-sm text-[#111827] mb-4 uppercase tracking-wider">Compétences</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach ($profile->skills as $skill)
                        <span class="text-xs font-medium text-[#0066CC] bg-blue-50 border border-blue-100 px-3 py-1">
                            {{ $skill->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Education --}}
        @if ($profile->education_start_year || $profile->education_end_year)
            <div class="bg-white border border-gray-200 p-6">
                <h2 class="font-bold text-sm text-[#111827] mb-4 uppercase tracking-wider">Formation à Bassila</h2>
                <p class="text-sm text-gray-600">
                    @if ($profile->education_start_year && $profile->education_end_year)
                        {{ $profile->education_start_year }} – {{ $profile->education_end_year }}
                    @elseif ($profile->education_start_year)
                        Depuis {{ $profile->education_start_year }}
                    @else
                        Jusqu'en {{ $profile->education_end_year }}
                    @endif
                </p>
            </div>
        @endif
    </div>

    {{-- Contact form --}}
    @auth
        @if (auth()->id() !== $profile->user_id)
            <div id="contact-form" class="mt-6">
                <livewire:contact.contact-form :profile="$profile" />
            </div>
        @endif
    @endauth

</div>

@endsection
