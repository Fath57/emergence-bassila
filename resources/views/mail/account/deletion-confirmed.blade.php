<p>Bonjour {{ $user->first_name }},</p>

<p>Votre demande de suppression a bien été confirmée. Votre compte est désormais désactivé.</p>

<p>La purge définitive aura lieu le <strong>{{ $purgeAt->isoFormat('D MMMM YYYY') }}</strong>. Jusqu'à cette date, vous pouvez annuler la demande en vous reconnectant à l'adresse habituelle du site.</p>

<p>L'équipe Bassila Emergence</p>
