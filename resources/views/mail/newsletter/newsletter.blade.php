<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $campaign->subject }}</title>
    <style>
        body { margin: 0; padding: 0; background: #F5F5F5; font-family: 'Helvetica Neue', Arial, sans-serif; }
        .wrapper { max-width: 600px; margin: 0 auto; background: #ffffff; }
        .header { background: #0A1628; padding: 28px 40px; }
        .header img { height: 36px; }
        .preview-text { color: #F5F5F5; font-size: 0; max-height: 0; overflow: hidden; }
        .body { padding: 40px; }
        .body h2, .body h3, .body h4 { color: #0A1628; margin: 24px 0 8px; }
        .body p { font-size: 15px; color: #333; line-height: 1.7; margin: 0 0 16px; }
        .body a { color: #0066CC; }
        .body ul, .body ol { font-size: 15px; color: #333; line-height: 1.7; padding-left: 20px; margin: 0 0 16px; }
        .body blockquote { border-left: 4px solid #0066CC; margin: 0 0 16px; padding: 8px 16px; background: #F0F6FF; color: #444; }
        .body img { max-width: 100%; height: auto; }
        .body pre { background: #F3F4F6; padding: 16px; overflow-x: auto; font-size: 13px; }
        .body code { background: #F3F4F6; padding: 2px 6px; font-size: 13px; }
        .body table { border-collapse: collapse; width: 100%; margin-bottom: 16px; }
        .body table th, .body table td { border: 1px solid #E5E7EB; padding: 8px 12px; font-size: 14px; }
        .body table th { background: #F9FAFB; font-weight: 600; }
        .footer { background: #0A1628; padding: 24px 40px; text-align: center; }
        .footer p { font-size: 11px; color: rgba(255,255,255,0.4); margin: 0 0 8px; }
        .footer a { color: rgba(255,255,255,0.5); text-decoration: underline; }
    </style>
</head>
<body>
<div class="wrapper">
    {{-- Preview text (hidden in email clients) --}}
    @if ($campaign->preview_text)
        <div class="preview-text">{{ $campaign->preview_text }}</div>
    @endif

    <div class="header">
        <img src="{{ asset('images/logo.png') }}" alt="Bassila Émergence">
    </div>

    <div class="body">
        {!! $campaign->content !!}
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} Bassila Émergence &mdash; Réseau communautaire</p>
        <p>
            Vous recevez cet email car vous êtes inscrit(e) à notre newsletter.<br>
            <a href="{{ url('/newsletter/desabonner/' . $subscriber->unsubscribe_token) }}">
                Se désabonner
            </a>
        </p>
    </div>

    {{-- 1x1 tracking pixel --}}
    <img src="{{ url('/newsletter/pixel/' . $send->open_token . '.gif') }}"
         width="1" height="1" alt="" style="display:block;border:0;margin:0;padding:0;">
</div>
</body>
</html>
