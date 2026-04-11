@section('title', 'Rédiger un article')
@section('description', 'Rédigez un nouvel article pour la communauté Bassila Émergence.')
@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

<div>

@push('head')
    @vite(['resources/js/editor.js'])
@endpush

<div class="max-w-4xl mx-auto px-5 py-10">

    <div class="flex items-center justify-between flex-wrap gap-3 mb-8">
        <div>
            <h1 class="font-serif text-3xl md:text-4xl font-semibold text-[#0A1628]">Rédiger un article</h1>
            @if ($autoSavedAt)
                <p class="text-xs text-gray-400 mt-2">Brouillon enregistré automatiquement.</p>
            @endif
        </div>
        @if ($post)
            <a href="{{ route('blog.preview', $post) }}"
               target="_blank"
               class="inline-flex items-center gap-2 text-sm font-semibold text-[#0066CC] border border-gray-200 px-4 py-2 hover:border-[#0066CC] transition">
                Aperçu
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>
        @endif
    </div>

    @if (session('info'))
        <div class="bg-blue-50 border border-blue-200 px-4 py-3 mb-6 text-sm text-blue-800">
            {{ session('info') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">

        {{-- Title --}}
        <div>
            <label for="title" class="block text-xs uppercase tracking-widest text-gray-500 font-semibold mb-2">Titre</label>
            <input wire:model.live.debounce.3000ms="title"
                   id="title"
                   type="text"
                   placeholder="Titre de votre article"
                   class="w-full border border-gray-300 px-4 py-3 font-serif text-2xl focus:outline-none focus:border-[#0066CC] @error('title') border-red-400 @enderror">
            @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Slug --}}
        <div>
            <label for="slug" class="block text-xs uppercase tracking-widest text-gray-500 font-semibold mb-2">URL</label>
            <div class="flex items-stretch border border-gray-300 @error('slug') border-red-400 @enderror">
                <span class="px-3 py-2 bg-gray-50 text-gray-400 text-sm border-r border-gray-300">/blog/</span>
                <input wire:model="slug"
                       id="slug"
                       type="text"
                       class="flex-1 px-3 py-2 text-sm focus:outline-none">
            </div>
            @error('slug') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Cover image --}}
        <div>
            <label class="block text-xs uppercase tracking-widest text-gray-500 font-semibold mb-2">Image de couverture</label>
            @if ($featuredImage)
                <img src="{{ $featuredImage->temporaryUrl() }}" class="mb-3 h-48 w-full object-cover border border-gray-200" alt="Aperçu">
            @elseif ($post && $post->featured_image_url)
                <img src="{{ $post->featured_image_url }}" class="mb-3 h-48 w-full object-cover border border-gray-200" alt="Image actuelle">
            @endif
            <input wire:model="featuredImage"
                   type="file"
                   accept="image/*"
                   class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-[#0066CC] hover:file:bg-gray-200 cursor-pointer">
            @error('featuredImage') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Content (TipTap) --}}
        <div>
            <label class="block text-xs uppercase tracking-widest text-gray-500 font-semibold mb-2">Contenu</label>
            <x-tiptap-editor :value="$content" wire-key="content" />
            @error('content') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Excerpt --}}
        <div>
            <label for="excerpt" class="block text-xs uppercase tracking-widest text-gray-500 font-semibold mb-2">Extrait <span class="normal-case text-gray-400">(optionnel)</span></label>
            <textarea wire:model="excerpt"
                      id="excerpt"
                      rows="3"
                      maxlength="500"
                      placeholder="Résumé affiché dans les listes"
                      class="w-full border border-gray-300 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] resize-none @error('excerpt') border-red-400 @enderror"></textarea>
            @error('excerpt') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- SEO panel --}}
        <details class="border border-gray-200">
            <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-[#0A1628] bg-gray-50">
                Référencement (SEO)
            </summary>
            <div class="p-4 space-y-4">
                <div>
                    <label for="metaTitle" class="block text-xs uppercase tracking-widest text-gray-500 font-semibold mb-2">Titre SEO</label>
                    <input wire:model="metaTitle"
                           id="metaTitle"
                           type="text"
                           maxlength="70"
                           placeholder="Par défaut : titre de l'article"
                           class="w-full border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:border-[#0066CC]">
                    <p class="mt-1 text-xs text-gray-400">Max 70 caractères (affichés dans Google).</p>
                </div>
                <div>
                    <label for="metaDescription" class="block text-xs uppercase tracking-widest text-gray-500 font-semibold mb-2">Description SEO</label>
                    <textarea wire:model="metaDescription"
                              id="metaDescription"
                              rows="2"
                              maxlength="160"
                              placeholder="Par défaut : début du contenu"
                              class="w-full border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:border-[#0066CC] resize-none"></textarea>
                    <p class="mt-1 text-xs text-gray-400">Max 160 caractères (affichés dans Google).</p>
                </div>
            </div>
        </details>

        {{-- Category + Status --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="category_id" class="block text-xs uppercase tracking-widest text-gray-500 font-semibold mb-2">Catégorie</label>
                <select wire:model="category_id" id="category_id" class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC]">
                    <option value="">Sans catégorie</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-xs uppercase tracking-widest text-gray-500 font-semibold mb-2">Statut</label>
                <select wire:model="status" id="status" class="w-full border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:border-[#0066CC]">
                    <option value="draft">Brouillon</option>
                    <option value="published">Publié</option>
                </select>
            </div>
        </div>

        <div class="flex gap-3 pt-4 border-t border-gray-200">
            <button type="submit"
                    class="inline-flex items-center justify-center bg-[#0066CC] hover:bg-[#0052A3] text-white font-semibold text-sm px-6 py-3 transition"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-75 cursor-not-allowed">
                <span wire:loading.remove>Enregistrer</span>
                <span wire:loading>Enregistrement…</span>
            </button>
            <a href="{{ route('blog.mine') }}"
               wire:navigate
               class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition">
                Annuler
            </a>
        </div>
    </form>
</div>
</div>
