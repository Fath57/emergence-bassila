@props([
    'name'     => 'content',
    'value'    => '',
    'wireKey'  => 'content',
    'autosave' => true,
])

<div data-editor
     wire:ignore
     class="border border-gray-300 bg-white">

    {{-- Toolbar --}}
    <div data-editor-toolbar
         class="flex flex-wrap items-center gap-1 border-b border-gray-200 px-2 py-2 bg-gray-50">

        @php
            $btn = 'inline-flex items-center justify-center w-9 h-9 text-sm font-semibold text-gray-600 hover:text-[#0066CC] hover:bg-white transition';
        @endphp

        <button type="button" data-cmd="bold"        class="{{ $btn }}" title="Gras"><strong>B</strong></button>
        <button type="button" data-cmd="italic"      class="{{ $btn }}" title="Italique"><em>I</em></button>
        <button type="button" data-cmd="strike"      class="{{ $btn }}" title="Barré"><s>S</s></button>
        <span class="w-px h-6 bg-gray-300 mx-1"></span>

        <button type="button" data-cmd="h2"          class="{{ $btn }}" title="Titre 2">H2</button>
        <button type="button" data-cmd="h3"          class="{{ $btn }}" title="Titre 3">H3</button>
        <button type="button" data-cmd="h4"          class="{{ $btn }}" title="Titre 4">H4</button>
        <span class="w-px h-6 bg-gray-300 mx-1"></span>

        <button type="button" data-cmd="bulletList"  class="{{ $btn }}" title="Liste à puces">•</button>
        <button type="button" data-cmd="orderedList" class="{{ $btn }}" title="Liste numérotée">1.</button>
        <button type="button" data-cmd="blockquote"  class="{{ $btn }}" title="Citation">❝</button>
        <button type="button" data-cmd="codeBlock"   class="{{ $btn }}" title="Bloc de code">{}</button>
        <button type="button" data-cmd="hr"          class="{{ $btn }}" title="Séparateur">―</button>
        <span class="w-px h-6 bg-gray-300 mx-1"></span>

        <button type="button" data-cmd="link"        class="{{ $btn }}" title="Lien">🔗</button>
        <button type="button" data-cmd="image"       class="{{ $btn }}" title="Image">🖼</button>
        <button type="button" data-cmd="youtube"     class="{{ $btn }}" title="YouTube">▶</button>
        <span class="w-px h-6 bg-gray-300 mx-1"></span>

        <button type="button" data-cmd="undo"        class="{{ $btn }}" title="Annuler">↶</button>
        <button type="button" data-cmd="redo"        class="{{ $btn }}" title="Rétablir">↷</button>
    </div>

    {{-- Editor mount point --}}
    <div data-editor-mount></div>

    {{-- Hidden textarea — Livewire binds here; TipTap JS writes HTML on update.
         autosave=true: .live.debounce.3000ms so updatedContent() fires for blog autosave.
         autosave=false: plain wire:model so the value is flushed on the same network
         trip as the form submit (used by the newsletter composer where there is no
         updatedContent hook to save incremental state). --}}
    <textarea
        data-editor-content
        @if ($autosave)
            wire:model.live.debounce.3000ms="{{ $wireKey }}"
        @else
            wire:model="{{ $wireKey }}"
        @endif
        name="{{ $name }}"
        class="hidden"
    >{{ $value }}</textarea>
</div>

{{-- ── Image insertion modal ──────────────────────────────────────────
     Replaces window.prompt() for alt text and window.alert() for upload
     failures. Lives outside the editor wrapper so wire:ignore doesn't
     prevent Livewire from seeing it, but is hidden by default. One
     modal instance is enough because only one editor is mounted at a
     time on any given page. --}}
<div data-editor-modal
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="tiptap-image-modal-title">

    <div class="bg-white max-w-lg w-full shadow-2xl" data-editor-modal-panel>
        {{-- Header --}}
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 id="tiptap-image-modal-title"
                class="font-serif text-xl font-semibold text-[#0A1628]">
                Ajouter une image
            </h3>
            <p class="text-sm text-gray-600 mt-1">
                Le texte alternatif décrit l'image pour les lecteurs d'écran et le référencement.
            </p>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5 space-y-4">

            {{-- Preview --}}
            <div data-editor-modal-preview class="hidden">
                <div class="aspect-video w-full bg-gray-50 border border-gray-200 overflow-hidden flex items-center justify-center">
                    <img data-editor-modal-preview-img
                         src=""
                         alt=""
                         class="max-h-full max-w-full object-contain">
                </div>
            </div>

            {{-- Upload spinner --}}
            <div data-editor-modal-spinner class="hidden flex items-center gap-3 text-sm text-gray-500">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-80" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Téléversement en cours…
            </div>

            {{-- Error banner --}}
            <div data-editor-modal-error
                 class="hidden bg-red-50 border border-red-200 text-red-800 text-sm px-3 py-2">
            </div>

            {{-- Alt text input --}}
            <div>
                <label for="tiptap-image-modal-alt"
                       class="block text-xs uppercase tracking-widest text-gray-500 font-semibold mb-2">
                    Texte alternatif
                </label>
                <input data-editor-modal-input
                       id="tiptap-image-modal-alt"
                       type="text"
                       maxlength="200"
                       placeholder="Ex: Une vue aérienne du village de Bassila"
                       class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC]">
                <p class="text-xs text-gray-400 mt-1">
                    Laissez vide uniquement si l'image est purement décorative.
                </p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3 bg-gray-50">
            <button type="button"
                    data-editor-modal-cancel
                    class="px-5 py-2 border border-gray-300 text-sm font-semibold text-gray-600 hover:bg-white transition">
                Annuler
            </button>
            <button type="button"
                    data-editor-modal-confirm
                    class="px-5 py-2 bg-[#0066CC] text-white text-sm font-semibold hover:bg-[#0052A3] transition disabled:opacity-50 disabled:cursor-not-allowed">
                Insérer l'image
            </button>
        </div>
    </div>
</div>
