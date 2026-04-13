<div class="max-w-md mx-auto text-center py-12">
    @if ($state === 'confirmed')
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Suppression confirmée</h1>
        <p class="text-sm text-gray-600">
            Votre compte est désormais désactivé. La suppression définitive interviendra le
            <strong>{{ $request->scheduled_purge_at->isoFormat('D MMMM YYYY') }}</strong>.
            Vous pouvez annuler à tout moment en vous reconnectant.
        </p>
    @elseif ($state === 'expired')
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Lien expiré</h1>
        <p class="text-sm text-gray-600">
            Ce lien de confirmation a expiré (validité 24 heures). Relancez une demande depuis votre profil si vous souhaitez toujours supprimer votre compte.
        </p>
    @else
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Demande déjà traitée</h1>
        <p class="text-sm text-gray-600">Cette demande a déjà été confirmée, annulée ou traitée.</p>
    @endif
</div>
