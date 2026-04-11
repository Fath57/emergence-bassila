<div>
    <div class="mb-8">
        <p class="text-xs font-semibold text-[#DC143C] uppercase tracking-widest mb-2">Invitation</p>
        <h1 class="text-2xl font-bold text-[#111827] mb-1">Créer votre compte</h1>
        <p class="text-sm text-gray-400">Complétez le formulaire pour rejoindre Bassila Émergence.</p>
    </div>

    <div class="bg-blue-50/50 border border-blue-100 p-4 mb-6">
        <p class="text-xs font-semibold text-[#0066CC] uppercase tracking-wider mb-1">Invitation pour</p>
        <p class="text-sm text-[#111827] font-semibold">{{ $invitation->email }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Rôle : <span class="font-semibold">{{ $invitation->role }}</span></p>
    </div>

    <form wire:submit.prevent="accept" class="space-y-5">

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="first_name" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                    Prénom
                </label>
                <input wire:model="first_name"
                       id="first_name" type="text" autocomplete="given-name"
                       placeholder="Prénom"
                       class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition @error('first_name') border-red-400 @enderror">
                @error('first_name') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="last_name" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                    Nom
                </label>
                <input wire:model="last_name"
                       id="last_name" type="text" autocomplete="family-name"
                       placeholder="Nom"
                       class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition @error('last_name') border-red-400 @enderror">
                @error('last_name') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                Mot de passe
            </label>
            <input wire:model="password"
                   id="password" type="password" autocomplete="new-password"
                   placeholder="8 caractères minimum"
                   class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition @error('password') border-red-400 @enderror">
            @error('password') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                Confirmer le mot de passe
            </label>
            <input wire:model="password_confirmation"
                   id="password_confirmation" type="password" autocomplete="new-password"
                   placeholder="Répétez le mot de passe"
                   class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition">
        </div>

        <button type="submit"
                class="w-full bg-[#0066CC] hover:bg-blue-800 text-white font-semibold py-3 text-sm transition"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-75">
            <span wire:loading.remove>Créer mon compte</span>
            <span wire:loading>Création en cours…</span>
        </button>
    </form>
</div>
