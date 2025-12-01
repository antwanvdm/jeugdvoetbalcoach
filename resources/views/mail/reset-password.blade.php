<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset je wachtwoord</title>
    <style>
        /* Basic, safe inline-friendly styles for most clients */
        body { margin:0; padding:0; background:#f5f7fb; }
        .wrapper { width:100%; background:#f5f7fb; padding:24px 0; }
        .container { max-width:600px; margin:0 auto; background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.06); }
        .header { background:#1d4ed8; color:#ffffff; padding:20px 24px; font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; }
        .header h1 { margin:0; font-size:20px; }
        .content { padding:24px; font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; color:#111827; }
        .button { display:inline-block; background:#1d4ed8; color:#ffffff; padding:12px 24px; text-decoration:none; border-radius:8px; font-weight:600; margin:16px 0; }
        .button:hover { background:#1e40af; }
        .footer { padding:16px 24px; color:#6b7280; font-size:12px; text-align:center; font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; }
        .brand { font-weight:700; color:#1d4ed8; }
        .note { background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:12px; margin:16px 0; font-size:13px; color:#4b5563; }
        .warning { background:#fef3c7; border:1px solid #fbbf24; border-radius:8px; padding:12px; margin:16px 0; font-size:13px; color:#78350f; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>{{ config('app.name') }}</h1>
            </div>
            <div class="content">
                <h2 style="color:#111827; font-size:18px; margin-top:0;">Wachtwoord vergeten?</h2>
                
                <p style="color:#374151; line-height:1.6;">Je ontvangt deze e-mail omdat we een verzoek hebben ontvangen om je wachtwoord te resetten. Klik op de knop hieronder om een nieuw wachtwoord in te stellen.</p>

                <div style="text-align:center; margin:24px 0;">
                    <a href="{{ $resetUrl }}" class="button" style="display:inline-block; background:#1d4ed8; color:#ffffff; padding:12px 24px; text-decoration:none; border-radius:8px; font-weight:600;">Reset wachtwoord</a>
                </div>

                <div class="warning">
                    <strong>⏱️ Let op:</strong> Deze link is 60 minuten geldig en kan maar één keer worden gebruikt.
                </div>

                <div class="note">
                    <strong>Link werkt niet?</strong><br>
                    Kopieer en plak deze URL in je browser:<br>
                    <a href="{{ $resetUrl }}" style="color:#1d4ed8; word-break:break-all;">{{ $resetUrl }}</a>
                </div>

                <p style="color:#6b7280; font-size:13px; margin-top:24px;">
                    Als je geen wachtwoord reset hebt aangevraagd, kun je deze e-mail negeren. Je wachtwoord blijft ongewijzigd.
                </p>
            </div>
            <div class="footer">
                <p>&copy; {{ date('Y') }} <span class="brand">{{ config('app.name') }}</span>. Alle rechten voorbehouden.</p>
            </div>
        </div>
    </div>
</body>
</html>
