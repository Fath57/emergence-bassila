<div>
    @if ($submitted)
        <div class="flex items-start gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 text-sm">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="font-medium">Commentaire soumis !</p>
                <p class="mt-0.5">Votre commentaire sera visible après modération.</p>
            </div>
        </div>
    @else
        <form wire:submit.prevent="submit" class="space-y-3">
            <div>
                <label for="comment-content" class="block text-sm font-medium text-gray-700 mb-1">Votre commentaire</label>
                <textarea
                    wire:model="content"
                    id="comment-content"
                    rows="4"
                    maxlength="1000"
                    placeholder="Partagez votre avis..."
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] resize-none @error('content') border-red-400 @enderror"
                ></textarea>
                @error('content') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <button
                type="submit"
                class="bg-[#0066CC] hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-75"
            >
                <span wire:loading.remove>Publier le commentaire</span>
                <span wire:loading>Envoi...</span>
            </button>
        </form>
    @endif
</div>
