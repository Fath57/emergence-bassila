<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Message envoyé - Bassila Network</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; background: #f5f5f5; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb;">
        <!-- Header -->
        <div style="background: #0066CC; padding: 24px 32px;">
            <h1 style="color: white; margin: 0; font-size: 20px;">Bassila Network</h1>
            <p style="color: rgba(255,255,255,0.8); margin: 4px 0 0; font-size: 13px;">Confirmation d'envoi</p>
        </div>

        <!-- Content -->
        <div style="padding: 32px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                <div style="width: 32px; height: 32px; background: #d1fae5; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <span style="color: #059669; font-size: 16px;">✓</span>
                </div>
                <p style="margin: 0; font-weight: 600; font-size: 15px; color: #059669;">Votre message a bien été envoyé !</p>
            </div>

            <p style="margin: 0 0 16px; color: #666;">
                Votre message à <strong>{{ $contactMessage->receiver->name }}</strong> a été transmis avec succès.
            </p>

            <!-- Message summary -->
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
                <p style="margin: 0 0 8px; font-size: 12px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em;">Destinataire</p>
                <p style="margin: 0 0 16px; font-weight: 600;">{{ $contactMessage->receiver->name }}</p>
                <p style="margin: 0 0 8px; font-size: 12px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em;">Sujet</p>
                <p style="margin: 0; font-weight: 500;">{{ $contactMessage->subject }}</p>
            </div>

            <p style="margin: 0; font-size: 13px; color: #9ca3af;">
                Si vous n'obtenez pas de réponse, vous pouvez envoyer un email directement à votre contact.<br>
                Merci d'utiliser Bassila Network !
            </p>
        </div>

        <!-- Footer -->
        <div style="background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 16px 32px; text-align: center;">
            <p style="margin: 0; font-size: 12px; color: #9ca3af;">© {{ date('Y') }} Bassila Network. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
