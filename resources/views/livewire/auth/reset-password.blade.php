@section('title', 'Réinitialiser le mot de passe')
@section('description', 'Définissez un nouveau mot de passe pour votre compte Bassila Émergence.')
@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-[#111827] mb-1">Nouveau mot de passe</h1>
        <p class="text-sm text-gray-400">Choisissez un nouveau mot de passe sécurisé.</p>
    </div>

    <form wire:submit.prevent="resetPassword" class="space-y-5">
        <input type="hidden" wire:model="token">

        <div>
            <label for="email" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                Adresse email
            </label>
            <input wire:model="email"
                   id="email" type="email" autocomplete="email"
                   class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition @error('email') border-red-400 @enderror">
            @error('email') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">
                Nouveau mot de passe
            </label>
            <input wire:model="password"
                   id="password" type="password" autocomplete="new-password"
                   placeholder="8 caractères minimum"
                   class="w-full border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:border-[#0066CC] transition @error('password') border-red-400 @enderror">
            @error('password') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
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
            <span wire:loading.remove>Réinitialiser le mot de passe</span>
            <span wire:loading>Réinitialisation…</span>
        </button>
    </form>
</div>
