<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lien invalide — Bassila Émergence</title>
    <style>
        body { margin: 0; padding: 60px 20px; background: #F5F5F5;
               font-family: 'Helvetica Neue', Arial, sans-serif; text-align: center; }
        .card { max-width: 480px; margin: 0 auto; background: #fff; padding: 48px; }
        h1 { font-size: 22px; color: #0A1628; margin: 0 0 12px; }
        p  { font-size: 15px; color: #555; line-height: 1.6; margin: 0 0 28px; }
        a  { display: inline-block; background: #0A1628; color: #fff; text-decoration: none;
             font-size: 14px; font-weight: 600; padding: 12px 28px; }
        .icon { font-size: 40px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">⚠️</div>
        <h1>Lien invalide ou expiré</h1>
        <p>Ce lien de confirmation n'est plus valide (il expire après 7 jours). Vous pouvez vous réinscrire via le formulaire pour recevoir un nouveau lien.</p>
        <a href="{{ route('home') }}">Retour au site</a>
    </div>
</body>
</html>
