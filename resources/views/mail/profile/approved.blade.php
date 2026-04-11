<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil vérifié — Bassila Émergence</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #111827; background: #f5f5f5; margin: 0; padding: 24px 12px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                       style="max-width: 600px; background: white; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">

                    <!-- Header -->
                    <tr>
                        <td style="background: white; padding: 28px 32px; border-bottom: 3px solid #0066CC;">
                            <img src="{{ asset('images/logo-trans.png') }}"
                                 alt="Bassila Émergence"
                                 width="180"
                                 style="display: block; height: auto; max-width: 180px; border: 0;">
                        </td>
                    </tr>

                    <!-- Subheader -->
                    <tr>
                        <td style="padding: 20px 32px 0;">
                            <p style="margin: 0; font-size: 11px; color: #059669; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;">
                                Profil vérifié
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 16px 32px 32px;">
                            <!-- Check badge -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin: 8px auto 20px;">
                                <tr>
                                    <td style="background: #d1fae5; width: 56px; height: 56px; border-radius: 50%; text-align: center; vertical-align: middle;">
                                        <span style="color: #059669; font-size: 28px; font-weight: 700; line-height: 56px;">&#10003;</span>
                                    </td>
                                </tr>
                            </table>

                            <h2 style="text-align: center; margin: 0 0 16px; font-size: 22px; color: #111827; font-weight: 700;">
                                Votre profil est vérifié !
                            </h2>

                            <p style="margin: 0 0 16px; color: #6b7280; font-size: 14px; line-height: 1.6;">
                                Bonjour <strong style="color: #111827;">{{ $profile->full_name }}</strong>,
                            </p>
                            <p style="margin: 0 0 24px; color: #6b7280; font-size: 14px; line-height: 1.6;">
                                Félicitations ! Votre profil a été examiné et vérifié par notre équipe.
                                Un badge de vérification est maintenant affiché sur votre profil public,
                                renforçant votre crédibilité au sein de la communauté Bassilaise.
                            </p>

                            <!-- CTA Button -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin: 24px auto;">
                                <tr>
                                    <td style="background: #0066CC; border-radius: 8px;">
                                        <a href="{{ route('profile.show', $profile) }}"
                                           style="display: inline-block; padding: 13px 28px; color: white; text-decoration: none; font-size: 14px; font-weight: 600; border-radius: 8px;">
                                            Voir mon profil
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 24px 0 0; font-size: 12px; color: #9ca3af; text-align: center; line-height: 1.5;">
                                Merci de contribuer à la communauté Bassilaise !
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background: #0A1628; padding: 18px 32px; text-align: center;">
                            <p style="margin: 0; font-size: 11px; color: rgba(255,255,255,0.4); letter-spacing: 0.05em;">
                                © {{ date('Y') }} Bassila Émergence. Tous droits réservés.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
