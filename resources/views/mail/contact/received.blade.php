<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nouveau message — Bassila Émergence</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #111827; background: #f5f5f5; margin: 0; padding: 24px 12px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                       style="max-width: 600px; background: white; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">

                    <!-- Header: white with logo + blue accent bar -->
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
                                Nouveau message reçu
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 16px 32px 32px;">
                            <p style="margin: 0 0 16px; font-size: 15px;">
                                Bonjour <strong>{{ $contactMessage->receiver->name }}</strong>,
                            </p>
                            <p style="margin: 0 0 24px; color: #6b7280; font-size: 14px; line-height: 1.6;">
                                <strong style="color: #111827;">{{ $contactMessage->sender->profile->full_name ?? $contactMessage->sender->name }}</strong>
                                vous a envoyé un message sur Bassila Émergence.
                            </p>

                            <!-- Message box -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 20px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="margin: 0 0 6px; font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Sujet</p>
                                        <p style="margin: 0 0 16px; font-weight: 600; font-size: 15px; color: #111827;">{{ $contactMessage->subject }}</p>
                                        <p style="margin: 0 0 6px; font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">Message</p>
                                        <p style="margin: 0; line-height: 1.6; white-space: pre-line; font-size: 14px;">{{ $contactMessage->message }}</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Sender info -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; margin-bottom: 20px;">
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <p style="margin: 0 0 4px; font-size: 11px; color: #1d4ed8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Coordonnées</p>
                                        <p style="margin: 0; font-size: 14px; font-weight: 600; color: #111827;">{{ $contactMessage->sender->profile->full_name ?? $contactMessage->sender->name }}</p>
                                        <p style="margin: 4px 0 0; font-size: 13px; color: #6b7280;">
                                            <a href="mailto:{{ $contactMessage->sender->email }}" style="color: #0066CC; text-decoration: none;">{{ $contactMessage->sender->email }}</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0; font-size: 12px; color: #9ca3af; line-height: 1.5;">
                                Pour répondre, envoyez un email directement à {{ $contactMessage->sender->email }}
                                ou visitez son profil sur Bassila Émergence.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer: dark navy matching site footer -->
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
