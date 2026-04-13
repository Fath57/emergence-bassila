<div class="max-w-xl mx-auto py-12 px-4">
    <h1 class="text-2xl font-bold text-gray-900 mb-4">Ton compte est en cours de suppression</h1>
    <p class="text-sm text-gray-700 mb-6">
        Ta demande est planifiée pour le
        <strong>{{ $request->scheduled_purge_at?->isoFormat('D MMMM YYYY') ?? '—' }}</strong>.
        Tu peux encore l'annuler&nbsp;: ton compte redeviendra actif immédiatement.
    </p>
    <button wire:click="cancel"
            class="bg-[#0066CC] hover:bg-blue-800 text-white font-semibold px-5 py-3 text-sm">
        Annuler la suppression et réactiver mon compte
    </button>
</div>
