<div>
    <div class="mb-8">
        <p class="text-xs font-semibold text-[#DC143C] uppercase tracking-widest mb-1">Newsletter</p>
        <h1 class="text-3xl font-bold text-[#111827]">Nouvelle campagne</h1>
    </div>

    <form wire:submit.prevent="save" class="space-y-6 max-w-3xl">
        {{-- Subject --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Sujet <span class="text-red-500">*</span></label>
            <input wire:model="subject" type="text" maxlength="255"
                   class="w-full px-4 py-2.5 border border-gray-300 focus:outline-none focus:border-[#0066CC] text-sm">
            @error('subject') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Preview text --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Texte d'aperçu</label>
            <input wire:model="previewText" type="text" maxlength="150" placeholder="Aperçu affiché par les clients mail (optionnel)"
                   class="w-full px-4 py-2.5 border border-gray-300 focus:outline-none focus:border-[#0066CC] text-sm">
            @error('previewText') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Content --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Contenu <span class="text-red-500">*</span></label>
            <x-tiptap-editor name="content" :value="$content" wire-key="content" :autosave="false" />
            @error('content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-[#0066CC] hover:bg-blue-700 text-white font-semibold px-6 py-2.5 text-sm transition"
                    wire:loading.attr="disabled">
                Enregistrer le brouillon
            </button>
            <a href="{{ route('admin.newsletter') }}" wire:navigate
               class="px-6 py-2.5 border border-gray-300 text-gray-700 text-sm hover:bg-gray-50 transition">
                Annuler
            </a>
        </div>
    </form>
</div>

@push('head')
    @vite(['resources/js/editor.js'])
@endpush
