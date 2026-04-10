<div class="bg-white border border-gray-200 p-5 hover:border-[#0066CC] transition group">
    <div class="flex items-start gap-4">

        {{-- Avatar --}}
        <a href="{{ route('profile.show', $profile) }}" class="shrink-0">
            @if ($profile->avatar_url)
                <img src="{{ $profile->avatar_url }}"
                     alt="{{ $profile->full_name }}"
                     class="h-12 w-12 object-cover border border-gray-100">
            @else
                <div class="h-12 w-12 bg-[#0066CC] flex items-center justify-center text-white text-base font-bold">
                    {{ strtoupper(substr($profile->full_name, 0, 1)) }}
                </div>
            @endif
        </a>

        {{-- Info --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1.5 flex-wrap">
                <a href="{{ route('profile.show', $profile) }}"
                   class="font-semibold text-sm text-[#111827] group-hover:text-[#0066CC] transition truncate">
                    {{ $profile->full_name }}
                </a>
                @if ($profile->is_verified)
                    <svg class="w-3.5 h-3.5 text-[#0066CC] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </div>
            <p class="text-xs text-gray-500 mt-0.5 truncate">
                {{ $profile->job_title }}@if ($profile->company)<span class="text-gray-300"> · </span>{{ $profile->company }}@endif
            </p>
            <p class="text-xs text-gray-400 mt-0.5">
                {{ $profile->city ? $profile->city . ', ' : '' }}{{ $profile->country }}
            </p>
            @if ($profile->sector)
                <span class="inline-block mt-2 text-xs bg-gray-100 text-gray-600 px-2 py-0.5">
                    {{ $profile->sector->name }}
                </span>
            @endif
        </div>
    </div>

    {{-- Skills preview --}}
    @if ($profile->relationLoaded('skills') && $profile->skills->count() > 0)
        <div class="flex flex-wrap gap-1 mt-4 pt-3 border-t border-gray-100">
            @foreach ($profile->skills->take(3) as $skill)
                <span class="text-xs text-[#0066CC] bg-blue-50 border border-blue-100 px-2 py-0.5">{{ $skill->name }}</span>
            @endforeach
            @if ($profile->skills->count() > 3)
                <span class="text-xs text-gray-400 self-center">+{{ $profile->skills->count() - 3 }}</span>
            @endif
        </div>
    @endif

    {{-- Link --}}
    <div class="mt-4">
        <a href="{{ route('profile.show', $profile) }}"
           class="block text-center text-sm font-semibold text-[#0066CC] border border-[#0066CC] py-1.5 hover:bg-[#0066CC] hover:text-white transition">
            Voir le profil
        </a>
    </div>
</div>
