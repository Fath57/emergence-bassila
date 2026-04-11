<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmez votre adresse email — Bassila Émergence</title>
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
                            <p style="margin: 0; font-size: 11px; color: #0066CC; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;">
                                Confirmation d'adresse email
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 16px 32px 32px;">
                            <h1 style="margin: 0 0 16px; font-size: 22px; font-weight: 700; color: #111827;">
                                Bienvenue{{ $firstName ? ', '.$firstName : '' }} !
                            </h1>
                            <p style="margin: 0 0 16px; color: #6b7280; font-size: 14px; line-height: 1.6;">
                                Merci de rejoindre <strong style="color: #111827;">Bassila Émergence</strong>,
                                la plateforme de networking des Bassilois à travers le monde.
                            </p>
                            <p style="margin: 0 0 24px; color: #6b7280; font-size: 14px; line-height: 1.6;">
                                Pour activer votre compte, confirmez votre adresse email en cliquant sur le bouton ci-dessous.
                            </p>

                            <!-- CTA Button -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin: 24px auto;">
                                <tr>
                                    <td style="background: #0066CC; border-radius: 8px;">
                                        <a href="{{ $verifyUrl }}"
                                           style="display: inline-block; padding: 13px 28px; color: white; text-decoration: none; font-size: 14px; font-weight: 600; border-radius: 8px;">
                                            Confirmer mon adresse email
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 24px 0 8px; font-size: 12px; color: #9ca3af; line-height: 1.5;">
                                Si le bouton ne fonctionne pas, copiez-collez ce lien dans votre navigateur&nbsp;:
                            </p>
                            <p style="margin: 0 0 24px; font-size: 12px; color: #6b7280; line-height: 1.5; word-break: break-all;">
                                <a href="{{ $verifyUrl }}" style="color: #0066CC; text-decoration: underline;">{{ $verifyUrl }}</a>
                            </p>

                            <p style="margin: 24px 0 0; font-size: 12px; color: #9ca3af; line-height: 1.5; text-align: center;">
                                Ce lien est valable pendant 60 minutes. Si vous n'êtes pas à l'origine de cette inscription, vous pouvez ignorer cet email.
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
