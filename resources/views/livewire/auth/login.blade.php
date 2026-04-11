<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-[#111827] mb-1">Connexion</h1>
        <p class="text-sm text-gray-400">Bon retour sur Bassila Émergence</p>
    </div>

    <form wire:submit.prevent="login" class="space-y-5">

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
                   id="password" type="password" autocomplete="current-password"
                   placeholder="••••••••"
                   class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition @error('password') border-red-400 @enderror">
            @error('password') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
                <input wire:model="remember" type="checkbox"
                       class="border-gray-300 text-[#0066CC] focus:ring-[#0066CC]">
                <span class="text-sm text-gray-600">Se souvenir de moi</span>
            </label>
            <a href="{{ route('password.request') }}"
               class="text-xs text-[#0066CC] hover:underline font-semibold uppercase tracking-wider"
               wire:navigate>Oublié ?</a>
        </div>

        <button type="submit"
                class="w-full bg-[#0066CC] hover:bg-blue-800 text-white font-semibold py-3 text-sm transition"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-75">
            <span wire:loading.remove>Se connecter</span>
            <span wire:loading>Connexion…</span>
        </button>
    </form>

    <p class="mt-8 pt-6 border-t border-gray-100 text-center text-sm text-gray-400">
        Pas encore membre ?
        <a href="{{ route('register') }}" class="text-[#0066CC] hover:underline font-semibold" wire:navigate>
            Créer un compte
        </a>
    </p>
</div>
