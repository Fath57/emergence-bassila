<div class="bg-white border border-gray-200 p-5 hover:border-[#0066CC] transition group h-full flex flex-col min-h-0">
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

    @php
        $whatsappDigits = preg_replace('/\D+/', '', (string) ($profile->whatsapp ?? ''));
    @endphp
    {{-- Contact info (respect visibility flags) --}}
    @if (($profile->show_phone && $profile->phone) || ($profile->show_email_contact && $profile->email_contact) || ($profile->show_whatsapp && $whatsappDigits !== ''))
        <div class="mt-3 pt-3 border-t border-gray-100 space-y-1.5">
            @if ($profile->show_email_contact && $profile->email_contact)
                <a href="mailto:{{ $profile->email_contact }}"
                   class="flex items-center gap-1.5 text-xs text-[#0066CC] hover:underline min-w-0">
                    <svg class="w-3.5 h-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25H4.5a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5H4.5a2.25 2.25 0 00-2.25 2.25m19.5 0l-9.75 6.75L2.25 6.75"/>
                    </svg>
                    <span class="truncate">{{ $profile->email_contact }}</span>
                </a>
            @endif
            @if ($profile->show_phone && $profile->phone)
                <a href="tel:{{ preg_replace('/\s+/', '', $profile->phone) }}"
                   class="flex items-center gap-1.5 text-xs text-[#0066CC] hover:underline min-w-0">
                    <svg class="w-3.5 h-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 6.75z"/>
                    </svg>
                    <span class="truncate">{{ $profile->phone }}</span>
                </a>
            @endif
            @if ($profile->show_whatsapp && $whatsappDigits !== '')
                <a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener noreferrer"
                   class="flex items-center gap-1.5 text-xs text-[#0066CC] hover:underline min-w-0">
                    <svg class="w-3.5 h-3.5 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    <span class="truncate">{{ $profile->whatsapp }}</span>
                </a>
            @endif
        </div>
    @endif

    {{-- Actions (mt-auto keeps buttons aligned at bottom when cards share a row height) --}}
    <div class="mt-auto pt-4">
        @if (auth()->check() && auth()->id() === $profile->user_id)
            <a href="{{ route('profile.show', $profile) }}"
               class="block text-center text-sm font-semibold text-[#0066CC] border border-[#0066CC] py-1.5 hover:bg-[#0066CC] hover:text-white transition">
                Voir le profil
            </a>
        @else
            <div class="flex gap-2">
                <a href="{{ route('profile.show', $profile) }}"
                   class="flex-1 text-center text-sm font-semibold text-[#0066CC] border border-[#0066CC] py-1.5 hover:bg-[#0066CC] hover:text-white transition">
                    Voir le profil
                </a>
                @auth
                    <a href="{{ route('profile.show', $profile) }}#contact-form"
                       class="flex-1 text-center text-sm font-semibold bg-[#0066CC] text-white py-1.5 hover:bg-blue-800 transition">
                        Contacter
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="flex-1 text-center text-sm font-semibold bg-[#0066CC] text-white py-1.5 hover:bg-blue-800 transition">
                        Contacter
                    </a>
                @endauth
            </div>
        @endif
    </div>
</div>
