<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmez votre inscription — Bassila Émergence</title>
    <style>
        body { margin: 0; padding: 0; background: #F5F5F5; font-family: 'Helvetica Neue', Arial, sans-serif; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; }
        .header { background: #0A1628; padding: 32px 40px; }
        .header img { height: 40px; }
        .body { padding: 40px; }
        h1 { font-size: 22px; color: #0A1628; margin: 0 0 16px; }
        p { font-size: 15px; color: #444; line-height: 1.6; margin: 0 0 16px; }
        .btn { display: inline-block; background: #DC143C; color: #ffffff; text-decoration: none;
               font-size: 15px; font-weight: 600; padding: 14px 32px; margin: 8px 0 24px; }
        .small { font-size: 13px; color: #888; }
        .footer { background: #F5F5F5; padding: 24px 40px; font-size: 12px; color: #999; text-align: center; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <img src="{{ asset('images/logo.png') }}" alt="Bassila Émergence">
    </div>
    <div class="body">
        <h1>Confirmez votre inscription</h1>

        @if ($subscriber->first_name)
            <p>Bonjour {{ $subscriber->first_name }},</p>
        @else
            <p>Bonjour,</p>
        @endif

        <p>
            Merci de votre intérêt pour la newsletter Bassila Émergence.
            Pour finaliser votre inscription et commencer à recevoir nos actualités, cliquez sur le bouton ci-dessous.
        </p>

        <a href="{{ url('/newsletter/confirmer/' . $subscriber->confirmation_token) }}" class="btn">
            Confirmer mon inscription
        </a>

        <p class="small">
            Ce lien est valable 7 jours. Si vous n'avez pas demandé cette inscription, ignorez ce message.
        </p>
    </div>
    <div class="footer">
        © {{ date('Y') }} Bassila Émergence &mdash; Réseau communautaire
    </div>
</div>
</body>
</html>
