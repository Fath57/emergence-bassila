<div>
    @if($pending)
        <p class="text-green-400 text-sm font-semibold">
            Vérifiez votre boite mail — un lien de confirmation vous a été envoyé.
        </p>
    @else
        <form wire:submit.prevent="subscribe" class="flex flex-col gap-3">
            <div class="flex flex-col sm:flex-row gap-3">
                <input
                    wire:model="firstName"
                    type="text"
                    placeholder="Prénom (optionnel)"
                    class="sm:w-36 px-4 py-3 bg-white/10 border border-white/20 text-white placeholder-white/40 text-sm focus:outline-none focus:border-white/60"
                >
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
            </div>
            @error('email')
                <p class="text-red-400 text-xs">{{ $message }}</p>
            @enderror
        </form>
    @endif
</div>
