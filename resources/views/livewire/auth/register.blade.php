<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-[#111827] mb-1" style="font-family: 'Lora', serif;">Créer un compte</h1>
        <p class="text-sm text-gray-400">Rejoignez la communauté EmergenceBassila</p>
    </div>

    <form wire:submit.prevent="register" class="space-y-5">

        <div>
            <label for="name" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                Nom complet
            </label>
            <input wire:model="name"
                   id="name" type="text" autocomplete="name"
                   placeholder="Votre nom"
                   class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition @error('name') border-red-400 @enderror">
            @error('name') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                Adresse email
            </label>
            <input wire:model="email"
                   id="email" type="email" autocomplete="email"
                   placeholder="vous@exemple.com"
                   class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition @error('email') border-red-400 @enderror">
            @error('email') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
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

    <p class="mt-8 pt-6 border-t border-gray-100 text-center text-sm text-gray-400">
        Déjà membre ?
        <a href="{{ route('login') }}" class="text-[#0066CC] hover:underline font-semibold" wire:navigate>
            Se connecter
        </a>
    </p>
</div>
