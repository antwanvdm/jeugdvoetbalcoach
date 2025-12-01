<!DOCTYPE html>
<html lang="nl">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Feedback ontvangen</title>
        <style>
                /* Basic, safe inline-friendly styles for most clients */
                body { margin:0; padding:0; background:#f5f7fb; }
                .wrapper { width:100%; background:#f5f7fb; padding:24px 0; }
                .container { max-width:600px; margin:0 auto; background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.06); }
                .header { background:#1d4ed8; color:#ffffff; padding:20px 24px; font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; }
                .header h1 { margin:0; font-size:20px; }
                .content { padding:24px; font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; color:#111827; }
                .meta { background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:16px; margin:16px 0; }
                .meta dt { font-weight:600; color:#374151; }
                .meta dd { margin:0 0 8px 0; color:#4b5563; }
                .message { white-space:pre-wrap; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px; padding:16px; color:#111827; }
                .footer { padding:16px 24px; color:#6b7280; font-size:12px; text-align:center; font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; }
                .brand { font-weight:700; color:#1d4ed8; }
                a.button { display:inline-block; background:#1d4ed8; color:#ffffff; padding:10px 16px; text-decoration:none; border-radius:8px; font-weight:600; }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <div class="container">
                <div class="header">
                    <h1>{{ config('app.name') }} – Feedback</h1>
                </div>
                <div class="content">
                    <p>Beste {{ $name }},</p>
                    <p>Dank voor je bericht op {{ config('app.name') }}. Wij zullen binnen 1 werkweek reageren op je vraag. Hieronder zit bijgevoegd een kopie van de data die wij hebben ontvangen.</p>

                    <div class="meta">
                        <dl>
                            <dt>Naam</dt>
                            <dd>{{ $name }}</dd>
                            <dt>E-mail</dt>
                            <dd>{{ $email }}</dd>
                            <dt>Onderwerp</dt>
                            <dd>{{ $subject }}</dd>
                        </dl>
                    </div>

                    <p><strong>Bericht</strong></p>
                    <div class="message">{{ $messageBody }}</div>

                    <p style="margin-top:16px; color:#6b7280; font-size:13px;">Deze mail is een kopie van de ingestuurde feedback.</p>
                </div>
                <div class="footer">
                    <p>&copy; {{ date('Y') }} <span class="brand">{{ config('app.name') }}</span>. Alle rechten voorbehouden.</p>
                </div>
            </div>
        </div>
    </body>
</html>
