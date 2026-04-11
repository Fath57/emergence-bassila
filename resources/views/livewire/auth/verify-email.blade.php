<div class="text-center">
    <div class="w-14 h-14 border-2 border-[#0066CC] flex items-center justify-center mx-auto mb-6">
        <svg class="w-7 h-7 text-[#0066CC]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
    </div>

    <h1 class="text-2xl font-bold text-[#111827] mb-3">
        Vérifiez votre email
    </h1>
    <p class="text-sm text-gray-500 leading-relaxed mb-8">
        Avant de continuer, veuillez cliquer sur le lien de vérification envoyé à votre adresse email. Si vous ne l'avez pas reçu, cliquez ci-dessous pour en envoyer un nouveau.
    </p>

    @if ($successMessage)
        <div class="bg-green-50 border border-green-200 px-4 py-3 mb-6">
            <p class="text-sm font-medium text-green-800">{{ $successMessage }}</p>
        </div>
    @endif

    <div class="flex flex-col gap-3">
        <button type="button"
                wire:click="resend"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-75"
                class="w-full bg-[#0066CC] hover:bg-blue-800 text-white font-semibold text-sm px-5 py-3 transition">
            <span wire:loading.remove wire:target="resend">Renvoyer l'email de vérification</span>
            <span wire:loading wire:target="resend">Envoi en cours…</span>
        </button>

        <button type="button"
                wire:click="logout"
                wire:loading.attr="disabled"
                class="w-full border border-gray-200 hover:border-gray-300 text-gray-600 font-semibold text-sm px-5 py-3 transition">
            Se déconnecter
        </button>
    </div>
</div>
