@props([
    'name'    => 'content',
    'value'   => '',
    'wireKey' => 'content',
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

    {{-- Hidden textarea — Livewire binds here; TipTap JS writes HTML on update --}}
    <textarea
        data-editor-content
        wire:model.live.debounce.3000ms="{{ $wireKey }}"
        name="{{ $name }}"
        class="hidden"
    >{{ $value }}</textarea>
</div>
