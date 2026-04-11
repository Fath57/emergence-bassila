<div>
    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-xs font-semibold text-[#DC143C] uppercase tracking-widest mb-1">Newsletter</p>
            <h1 class="text-3xl font-bold text-[#111827]">Aperçu — {{ $campaign->subject }}</h1>
        </div>
        <a href="{{ route('admin.newsletter.edit', $campaign) }}" wire:navigate
           class="text-sm px-5 py-2.5 border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
            ← Retour à l'édition
        </a>
    </div>

    {{-- Meta info bar --}}
    <div class="bg-gray-50 border border-gray-200 px-5 py-3 mb-4 flex flex-wrap gap-6 text-sm text-gray-600">
        <div><span class="font-semibold text-gray-800">Sujet :</span> {{ $campaign->subject }}</div>
        @if ($campaign->preview_text)
            <div><span class="font-semibold text-gray-800">Aperçu :</span> {{ $campaign->preview_text }}</div>
        @endif
    </div>

    {{-- Iframe embedding the bare email HTML --}}
    <div class="border border-gray-200 bg-white">
        <iframe
            src="{{ route('admin.newsletter.html', $campaign) }}"
            class="w-full"
            style="min-height: 700px; border: 0;"
            onload="this.style.height=(this.contentWindow.document.body.scrollHeight+40)+'px'">
        </iframe>
    </div>
</div>
