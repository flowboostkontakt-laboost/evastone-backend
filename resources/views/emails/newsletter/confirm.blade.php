@php($t = [
    'de' => ['hi' => 'Guten Tag', 'lead' => 'bitte bestätigen Sie mit einem Klick, dass Sie den EvaStone-Newsletter erhalten möchten.', 'btn' => 'Anmeldung bestätigen', 'ignore' => 'Wenn Sie sich nicht angemeldet haben, ignorieren Sie diese E-Mail — ohne Bestätigung senden wir nichts.', 'consent' => 'Ihre Einwilligung'],
    'en' => ['hi' => 'Hello', 'lead' => 'please confirm with one click that you would like to receive the EvaStone newsletter.', 'btn' => 'Confirm subscription', 'ignore' => 'If you did not sign up, please ignore this email — without confirmation we send nothing.', 'consent' => 'Your consent'],
    'pl' => ['hi' => 'Dzień dobry', 'lead' => 'potwierdź jednym kliknięciem, że chcesz otrzymywać newsletter EvaStone.', 'btn' => 'Potwierdź zapis', 'ignore' => 'Jeśli to nie Ty — zignoruj tę wiadomość; bez potwierdzenia nic nie wyślemy.', 'consent' => 'Twoja zgoda'],
])
@php($m = $t[$locale] ?? $t['de'])

<x-mail::message>
{{ $m['hi'] }},

{{ $m['lead'] }}

<x-mail::button :url="$confirmUrl">
{{ $m['btn'] }}
</x-mail::button>

{{ $m['ignore'] }}

---

**{{ $m['consent'] }}:** {{ $wording }}

{{-- Dok. 10 wariant B — oznaczenie AI nad danymi nadawcy; ostateczna treść stopki/impressum: KONIK+prawnik --}}
_{{ config('evastone.newsletter.from_name') }}_
</x-mail::message>
