<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau message sur Bassila Network</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; background: #f5f5f5; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb;">
        <!-- Header -->
        <div style="background: #0066CC; padding: 24px 32px;">
            <h1 style="color: white; margin: 0; font-size: 20px;">Bassila Network</h1>
            <p style="color: rgba(255,255,255,0.8); margin: 4px 0 0; font-size: 13px;">Vous avez reçu un nouveau message</p>
        </div>

        <!-- Content -->
        <div style="padding: 32px;">
            <p style="margin: 0 0 16px;">Bonjour <strong>{{ $contactMessage->receiver->name }}</strong>,</p>
            <p style="margin: 0 0 24px; color: #666;">
                <strong>{{ $contactMessage->sender->profile->full_name ?? $contactMessage->sender->name }}</strong>
                vous a envoyé un message sur Bassila Network.
            </p>

            <!-- Message box -->
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
                <p style="margin: 0 0 8px; font-size: 12px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em;">Sujet</p>
                <p style="margin: 0 0 16px; font-weight: 600; font-size: 15px;">{{ $contactMessage->subject }}</p>
                <p style="margin: 0 0 8px; font-size: 12px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em;">Message</p>
                <p style="margin: 0; line-height: 1.6; white-space: pre-line;">{{ $contactMessage->message }}</p>
            </div>

            <!-- Sender info -->
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
                <p style="margin: 0 0 4px; font-size: 12px; color: #1d4ed8; font-weight: 600;">Coordonnées de l'expéditeur</p>
                <p style="margin: 0; font-size: 14px;">{{ $contactMessage->sender->profile->full_name ?? $contactMessage->sender->name }}</p>
                <p style="margin: 4px 0 0; font-size: 13px; color: #666;">Email : <a href="mailto:{{ $contactMessage->sender->email }}" style="color: #0066CC;">{{ $contactMessage->sender->email }}</a></p>
            </div>

            <p style="margin: 0; font-size: 13px; color: #9ca3af;">
                Pour répondre, envoyez un email directement à {{ $contactMessage->sender->email }}<br>
                ou visitez le profil de l'expéditeur sur Bassila Network.
            </p>
        </div>

        <!-- Footer -->
        <div style="background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 16px 32px; text-align: center;">
            <p style="margin: 0; font-size: 12px; color: #9ca3af;">© {{ date('Y') }} Bassila Network. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
