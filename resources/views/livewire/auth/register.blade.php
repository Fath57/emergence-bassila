@section('title', 'Inscription')
@section('description', 'Créez votre compte Bassila Émergence et rejoignez le réseau des Bassilois.')
@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-[#111827] mb-1">Créer un compte</h1>
        <p class="text-sm text-gray-400">Rejoignez la communauté Bassila Émergence</p>
    </div>

    @include('partials.google-auth-button', ['label' => "S'inscrire avec Google"])

    <form wire:submit.prevent="register" class="space-y-5">

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
            <div class="relative" x-data="{ show: false }">
                <input wire:model="password"
                       id="password" :type="show ? 'text' : 'password'" autocomplete="new-password"
                       placeholder="8 caractères minimum"
                       class="w-full border border-gray-200 px-4 py-3 pr-10 text-sm focus:outline-none focus:border-[#0066CC] transition @error('password') border-red-400 @enderror">
                <button type="button" @click="show = !show"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600 transition">
                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 012.654-4.592M9.88 9.88a3 3 0 104.24 4.24M3 3l18 18"/>
                    </svg>
                </button>
            </div>
            @error('password') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                Confirmer le mot de passe
            </label>
            <div class="relative" x-data="{ show: false }">
                <input wire:model="password_confirmation"
                       id="password_confirmation" :type="show ? 'text' : 'password'" autocomplete="new-password"
                       placeholder="Répétez le mot de passe"
                       class="w-full border border-gray-200 px-4 py-3 pr-10 text-sm focus:outline-none focus:border-[#0066CC] transition">
                <button type="button" @click="show = !show"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600 transition">
                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 012.654-4.592M9.88 9.88a3 3 0 104.24 4.24M3 3l18 18"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex items-start gap-2">
            <input wire:model="accepts_terms" id="accepts_terms" type="checkbox"
                   class="mt-1 h-4 w-4 border-gray-300 text-[#0066CC] focus:ring-[#0066CC]">
            <label for="accepts_terms" class="text-sm text-gray-600 leading-snug">
                J'accepte les
                <a href="{{ route('pages.cgu') }}" target="_blank" class="text-[#0066CC] hover:underline">CGU</a>
                et la
                <a href="{{ route('pages.privacy') }}" target="_blank" class="text-[#0066CC] hover:underline">Politique de confidentialité</a>.
            </label>
        </div>
        @error('accepts_terms') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

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
