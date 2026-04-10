<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande de vérification - Bassila Network</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; background: #f5f5f5; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb;">
        <!-- Header -->
        <div style="background: #0066CC; padding: 24px 32px;">
            <h1 style="color: white; margin: 0; font-size: 20px;">Bassila Network</h1>
        </div>

        <!-- Content -->
        <div style="padding: 32px;">
            <h2 style="margin: 0 0 16px; font-size: 18px;">Concernant votre demande de vérification</h2>
            <p style="margin: 0 0 16px; color: #666;">Bonjour <strong>{{ $profile->full_name }}</strong>,</p>
            <p style="margin: 0 0 24px; color: #666; line-height: 1.6;">
                Nous avons examiné votre demande de vérification de profil. Malheureusement, nous ne pouvons pas approuver votre profil à ce stade.
            </p>

            @if ($reason)
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
                    <p style="margin: 0 0 8px; font-size: 12px; color: #dc2626; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Raison</p>
                    <p style="margin: 0; color: #666; font-size: 14px;">{{ $reason }}</p>
                </div>
            @endif

            <p style="margin: 0 0 16px; color: #666; line-height: 1.6;">
                Vous pouvez mettre à jour votre profil et soumettre une nouvelle demande de vérification.
            </p>

            <div style="text-align: center; margin: 24px 0;">
                <a href="{{ route('profile.edit') }}" style="background: #0066CC; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; display: inline-block;">
                    Modifier mon profil
                </a>
            </div>

            <p style="margin: 24px 0 0; font-size: 13px; color: #9ca3af; text-align: center;">
                Pour toute question, contactez-nous sur la plateforme.
            </p>
        </div>

        <!-- Footer -->
        <div style="background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 16px 32px; text-align: center;">
            <p style="margin: 0; font-size: 12px; color: #9ca3af;">© {{ date('Y') }} Bassila Network. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
