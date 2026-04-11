<div>
    <div class="flex items-start justify-between mb-8">
        <div>
            <p class="text-xs font-semibold text-[#DC143C] uppercase tracking-widest mb-1">Newsletter</p>
            <h1 class="text-3xl font-bold text-[#111827]">Modifier la campagne</h1>
        </div>
        @if ($campaign->isEditable())
            <button wire:click="$set('showLaunchModal', true)"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 text-sm transition">
                Lancer l'envoi →
            </button>
        @else
            <span class="text-sm text-gray-500 italic">
                @php
                    $label = match($campaign->status) {
                        'sending' => 'En cours d\'envoi…',
                        'sent'    => 'Envoyée le ' . $campaign->sent_at?->format('d/m/Y'),
                        'failed'  => 'Envoi échoué',
                        default   => $campaign->status,
                    };
                @endphp
                {{ $label }}
            </span>
        @endif
    </div>

    {{-- Stats row (if sent) --}}
    @if ($campaign->status === 'sent')
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-white border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-[#111827]">{{ $campaign->sent_count }}</p>
                <p class="text-xs text-gray-500 uppercase tracking-wider mt-1">Envoyés</p>
            </div>
            <div class="bg-white border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-[#111827]">{{ $campaign->opens_count }}</p>
                <p class="text-xs text-gray-500 uppercase tracking-wider mt-1">Ouvertures</p>
            </div>
            <div class="bg-white border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-[#111827]">{{ $campaign->openRate() }} %</p>
                <p class="text-xs text-gray-500 uppercase tracking-wider mt-1">Taux d'ouverture</p>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6 max-w-3xl">
        {{-- Subject --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Sujet <span class="text-red-500">*</span></label>
            <input wire:model="subject" type="text" maxlength="255"
                   @if(!$campaign->isEditable()) disabled @endif
                   class="w-full px-4 py-2.5 border border-gray-300 focus:outline-none focus:border-[#0066CC] text-sm disabled:bg-gray-50">
            @error('subject') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Preview text --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Texte d'aperçu</label>
            <input wire:model="previewText" type="text" maxlength="150"
                   @if(!$campaign->isEditable()) disabled @endif
                   class="w-full px-4 py-2.5 border border-gray-300 focus:outline-none focus:border-[#0066CC] text-sm disabled:bg-gray-50">
        </div>

        {{-- Content --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Contenu <span class="text-red-500">*</span></label>
            @if ($campaign->isEditable())
                <x-tiptap-editor name="content" :value="$content" wire-key="content" :autosave="false" />
            @else
                <div class="prose prose-sm max-w-none border border-gray-200 p-4 bg-gray-50">
                    {!! $campaign->content !!}
                </div>
            @endif
            @error('content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        @if ($campaign->isEditable())
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="bg-[#0066CC] hover:bg-blue-700 text-white font-semibold px-6 py-2.5 text-sm transition"
                        wire:loading.attr="disabled">
                    Enregistrer
                </button>
                <a href="{{ route('admin.newsletter') }}" wire:navigate
                   class="px-6 py-2.5 border border-gray-300 text-gray-700 text-sm hover:bg-gray-50 transition">
                    ← Retour
                </a>
            </div>
        @else
            <div class="pt-2">
                <a href="{{ route('admin.newsletter') }}" wire:navigate
                   class="px-6 py-2.5 border border-gray-300 text-gray-700 text-sm hover:bg-gray-50 transition">
                    ← Retour
                </a>
            </div>
        @endif
    </form>

    {{-- Launch modal --}}
    @if ($showLaunchModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
            <div class="bg-white p-6 max-w-sm w-full">
                <h3 class="font-bold text-[#111827] mb-3">Confirmer l'envoi</h3>
                <p class="text-sm text-gray-600 mb-1">
                    La campagne sera envoyée à
                    <strong class="text-[#111827]">{{ number_format($activeCount) }} abonné{{ $activeCount > 1 ? 's' : '' }}</strong>
                    confirmé{{ $activeCount > 1 ? 's' : '' }}.
                </p>
                <p class="text-sm text-red-600 font-semibold mb-5">Cette action ne peut pas être annulée.</p>
                <div class="flex gap-3 justify-end">
                    <button wire:click="$set('showLaunchModal', false)"
                            class="text-sm px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button wire:click="launch"
                            class="text-sm px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>Envoyer maintenant</span>
                        <span wire:loading>Lancement…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@push('head')
    @vite(['resources/js/editor.js'])
@endpush
