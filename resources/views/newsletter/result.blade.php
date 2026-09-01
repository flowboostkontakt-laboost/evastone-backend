@php($copy = [
    'confirm' => [
        'de' => ['ok' => 'Danke — Ihre Anmeldung ist bestätigt.', 'bad' => 'Dieser Bestätigungslink ist ungültig oder abgelaufen.'],
        'en' => ['ok' => 'Thank you — your subscription is confirmed.', 'bad' => 'This confirmation link is invalid or has expired.'],
        'pl' => ['ok' => 'Dziękujemy — zapis potwierdzony.', 'bad' => 'Ten link potwierdzający jest nieprawidłowy lub wygasł.'],
    ],
    'unsubscribe' => [
        'de' => ['ok' => 'Sie wurden abgemeldet. Wir senden Ihnen nichts mehr.', 'bad' => 'Dieser Abmeldelink ist ungültig.'],
        'en' => ['ok' => 'You have been unsubscribed. We will not email you again.', 'bad' => 'This unsubscribe link is invalid.'],
        'pl' => ['ok' => 'Rezygnacja przyjęta. Nie wyślemy już żadnej wiadomości.', 'bad' => 'Ten link rezygnacji jest nieprawidłowy.'],
    ],
])
@php($m = $copy[$kind][$locale] ?? $copy[$kind]['de'])
<!doctype html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>EvaStone — Newsletter</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #efe7da; color: #1f1c17; display: grid; place-items: center; min-height: 100vh; margin: 0; }
        .box { max-width: 32rem; padding: 2.5rem; text-align: center; }
        h1 { font-weight: 600; font-size: 1.4rem; }
    </style>
</head>
<body>
    <div class="box">
        <h1>{{ $ok ? $m['ok'] : $m['bad'] }}</h1>
    </div>
</body>
</html>
