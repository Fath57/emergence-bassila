@section('title', 'Mot de passe oublié')
@section('description', 'Récupérez l\'accès à votre compte Bassila Émergence.')
@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-[#111827] mb-1">Mot de passe oublié</h1>
        <p class="text-sm text-gray-400">Entrez votre email pour recevoir un lien de réinitialisation.</p>
    </div>

    @if ($successMessage)
        <div class="mb-5 bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
            {{ $successMessage }}
        </div>
    @endif

    @if ($errorMessage)
        <div class="mb-5 bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
            {{ $errorMessage }}
        </div>
    @endif

    <form wire:submit.prevent="sendLink" class="space-y-5">
        <div>
            <label for="email" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                Adresse email
            </label>
            <input wire:model="email"
                   id="email" type="email" autocomplete="email"
                   placeholder="vous@exemple.com"
                   class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition @error('email') border-red-400 @enderror">
            @error('email') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <button type="submit"
                class="w-full bg-[#0066CC] hover:bg-blue-800 text-white font-semibold py-3 text-sm transition"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-75">
            <span wire:loading.remove>Envoyer le lien</span>
            <span wire:loading>Envoi en cours…</span>
        </button>
    </form>

    <p class="mt-8 pt-6 border-t border-gray-100 text-center text-sm text-gray-400">
        <a href="{{ route('login') }}" class="text-[#0066CC] hover:underline font-semibold" wire:navigate>
            ← Retour à la connexion
        </a>
    </p>
</div>
