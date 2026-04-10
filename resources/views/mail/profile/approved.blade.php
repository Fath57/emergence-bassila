<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil vérifié - Bassila Network</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; background: #f5f5f5; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb;">
        <!-- Header -->
        <div style="background: #0066CC; padding: 24px 32px;">
            <h1 style="color: white; margin: 0; font-size: 20px;">Bassila Network</h1>
        </div>

        <!-- Content -->
        <div style="padding: 32px;">
            <div style="font-size: 32px; text-align: center; margin-bottom: 16px;">✅</div>
            <h2 style="text-align: center; margin: 0 0 16px; font-size: 20px; color: #0066CC;">Votre profil est vérifié !</h2>
            <p style="margin: 0 0 16px; color: #666;">Bonjour <strong>{{ $profile->full_name }}</strong>,</p>
            <p style="margin: 0 0 24px; color: #666; line-height: 1.6;">
                Félicitations ! Votre profil a été examiné et vérifié par notre équipe. Un badge de vérification est maintenant affiché sur votre profil public, renforçant votre crédibilité au sein de la communauté Bassilaise.
            </p>

            <div style="text-align: center; margin: 24px 0;">
                <a href="{{ route('profile.show', $profile) }}" style="background: #0066CC; color: white; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; display: inline-block;">
                    Voir mon profil
                </a>
            </div>

            <p style="margin: 24px 0 0; font-size: 13px; color: #9ca3af; text-align: center;">
                Merci de contribuer à la communauté Bassilaise !
            </p>
        </div>

        <!-- Footer -->
        <div style="background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 16px 32px; text-align: center;">
            <p style="margin: 0; font-size: 12px; color: #9ca3af;">© {{ date('Y') }} Bassila Network. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
