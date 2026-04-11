@if (! empty($items))
<nav aria-label="Fil d'ariane" class="mb-6 text-sm text-gray-400 flex items-center gap-2 flex-wrap">
    @foreach ($items as $item)
        @if (! empty($item['url']))
            <a href="{{ $item['url'] }}" class="hover:text-[#0066CC] transition" wire:navigate>{{ $item['name'] }}</a>
        @else
            <span class="text-gray-700 truncate">{{ $item['name'] }}</span>
        @endif
        @unless ($loop->last)
            <span aria-hidden="true">/</span>
        @endunless
    @endforeach
</nav>

@if ($withJsonLd)
    {{-- Inline JSON-LD. Google accepts ld+json anywhere in the document.
         Use a single <x-breadcrumbs> per page to avoid duplicated blocks. --}}
    <x-seo.json-ld :data="\App\Support\Seo\StructuredData::breadcrumb($items)" />
@endif
@endif
