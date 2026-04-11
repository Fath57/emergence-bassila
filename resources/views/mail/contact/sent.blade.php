<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Message envoyé — Bassila Émergence</title>
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
                                Confirmation d'envoi
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 16px 32px 32px;">
                            <!-- Success badge -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 20px;">
                                <tr>
                                    <td style="background: #d1fae5; width: 36px; height: 36px; border-radius: 50%; text-align: center; vertical-align: middle;">
                                        <span style="color: #059669; font-size: 18px; font-weight: 700;">&#10003;</span>
                                    </td>
                                    <td style="padding-left: 12px;">
                                        <p style="margin: 0; font-weight: 600; font-size: 15px; color: #059669;">
                                            Votre message a bien été envoyé
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 20px; color: #6b7280; font-size: 14px; line-height: 1.6;">
                                Votre message à <strong style="color: #111827;">{{ $contactMessage->receiver->name }}</strong> a été transmis avec succès.
                            </p>

                            <!-- Message summary -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 20px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="margin: 0 0 6px; font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Destinataire</p>
                                        <p style="margin: 0 0 16px; font-weight: 600; font-size: 15px; color: #111827;">{{ $contactMessage->receiver->name }}</p>
                                        <p style="margin: 0 0 6px; font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Sujet</p>
                                        <p style="margin: 0; font-weight: 500; font-size: 14px;">{{ $contactMessage->subject }}</p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0; font-size: 12px; color: #9ca3af; line-height: 1.5;">
                                Si vous n'obtenez pas de réponse, vous pouvez envoyer un email directement à votre contact.
                                Merci d'utiliser Bassila Émergence !
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
