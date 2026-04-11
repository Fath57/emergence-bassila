<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demande de vérification — Bassila Émergence</title>
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
                            <p style="margin: 0; font-size: 11px; color: #DC143C; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;">
                                Demande de vérification
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 16px 32px 32px;">
                            <h2 style="margin: 0 0 16px; font-size: 18px; color: #111827; font-weight: 700;">
                                Concernant votre demande de vérification
                            </h2>

                            <p style="margin: 0 0 16px; color: #6b7280; font-size: 14px; line-height: 1.6;">
                                Bonjour <strong style="color: #111827;">{{ $profile->full_name }}</strong>,
                            </p>
                            <p style="margin: 0 0 24px; color: #6b7280; font-size: 14px; line-height: 1.6;">
                                Nous avons examiné votre demande de vérification de profil.
                                Malheureusement, nous ne pouvons pas approuver votre profil à ce stade.
                            </p>

                            @if ($reason)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                       style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; margin-bottom: 24px;">
                                    <tr>
                                        <td style="padding: 16px 20px;">
                                            <p style="margin: 0 0 6px; font-size: 11px; color: #dc2626; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                                                Raison
                                            </p>
                                            <p style="margin: 0; color: #7f1d1d; font-size: 14px; line-height: 1.6;">{{ $reason }}</p>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <p style="margin: 0 0 8px; color: #6b7280; font-size: 14px; line-height: 1.6;">
                                Vous pouvez mettre à jour votre profil et soumettre une nouvelle demande de vérification.
                            </p>

                            <!-- CTA Button -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin: 24px auto;">
                                <tr>
                                    <td style="background: #0066CC; border-radius: 8px;">
                                        <a href="{{ route('profile.edit') }}"
                                           style="display: inline-block; padding: 13px 28px; color: white; text-decoration: none; font-size: 14px; font-weight: 600; border-radius: 8px;">
                                            Modifier mon profil
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 24px 0 0; font-size: 12px; color: #9ca3af; text-align: center; line-height: 1.5;">
                                Pour toute question, contactez-nous sur la plateforme.
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
