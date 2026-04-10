<div>
    @if($subscribed)
        <p class="text-green-400 text-sm font-semibold">
            Merci ! Vous recevrez les actualités de la communauté.
        </p>
    @else
        <form wire:submit.prevent="subscribe" class="flex flex-col sm:flex-row gap-3">
            <input
                wire:model="email"
                type="email"
                placeholder="votre@email.com"
                class="flex-1 px-4 py-3 bg-white/10 border border-white/20 text-white placeholder-white/40 text-sm focus:outline-none focus:border-white/60"
            >
            <button
                type="submit"
                class="bg-[#DC143C] hover:bg-red-700 text-white font-semibold px-6 py-3 text-sm transition shrink-0"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>S'inscrire</span>
                <span wire:loading>…</span>
            </button>
        </form>
        @error('email')
            <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
        @enderror
    @endif
</div>
