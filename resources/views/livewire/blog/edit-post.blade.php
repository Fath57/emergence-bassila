<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-2xl font-bold text-[#333333] mb-6">Modifier l'article</h1>

        <form wire:submit.prevent="save" class="space-y-5">

            <!-- Featured image -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Image de couverture</label>
                @if ($post->featured_image_url && ! $featuredImage)
                    <img src="{{ $post->featured_image_url }}" class="mb-2 h-32 w-full object-cover rounded-lg" alt="Image actuelle">
                    <p class="text-xs text-gray-400 mb-2">Image actuelle — choisissez une nouvelle image pour la remplacer.</p>
                @endif
                @if ($featuredImage)
                    <img src="{{ $featuredImage->temporaryUrl() }}" class="mb-2 h-32 w-full object-cover rounded-lg border-2 border-[#0066CC]" alt="Nouvelle image">
                @endif
                <input
                    wire:model="featuredImage"
                    type="file"
                    accept="image/*"
                    class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-[#0066CC] hover:file:bg-blue-100 cursor-pointer"
                >
                @error('featuredImage') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Titre <span class="text-red-500">*</span></label>
                <input
                    wire:model.live.debounce.300ms="title"
                    id="title"
                    type="text"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] @error('title') border-red-400 @enderror"
                >
                @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <!-- Slug -->
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">URL (slug)</label>
                <div class="flex items-center rounded-lg border border-gray-300 overflow-hidden @error('slug') border-red-400 @enderror">
                    <span class="px-3 py-2 bg-gray-50 text-gray-400 text-sm border-r border-gray-300">/blog/</span>
                    <input
                        wire:model="slug"
                        id="slug"
                        type="text"
                        class="flex-1 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] focus:ring-inset"
                    >
                </div>
                @error('slug') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <!-- Category + Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                    <select wire:model="category_id" id="category_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC]">
                        <option value="">Sans catégorie</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select wire:model="status" id="status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC]">
                        <option value="draft">Brouillon</option>
                        <option value="published">Publié</option>
                        <option value="archived">Archivé</option>
                    </select>
                </div>
            </div>

            <!-- Excerpt -->
            <div>
                <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-1">Extrait</label>
                <textarea
                    wire:model="excerpt"
                    id="excerpt"
                    rows="2"
                    maxlength="500"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] resize-none @error('excerpt') border-red-400 @enderror"
                ></textarea>
                @error('excerpt') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Contenu <span class="text-red-500">*</span></label>
                <textarea
                    wire:model="content"
                    id="content"
                    rows="16"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] resize-y font-mono @error('content') border-red-400 @enderror"
                ></textarea>
                @error('content') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button
                    type="submit"
                    class="flex-1 bg-[#0066CC] hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition text-sm"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-75 cursor-not-allowed"
                >
                    <span wire:loading.remove>Enregistrer les modifications</span>
                    <span wire:loading>Enregistrement...</span>
                </button>
                <a href="{{ route('blog.show', $post->slug) }}" class="px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-600 hover:bg-gray-50 transition" wire:navigate>
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
