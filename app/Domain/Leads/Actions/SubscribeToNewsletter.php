<?php

declare(strict_types=1);

namespace App\Domain\Leads\Actions;

use App\Domain\Consents\Models\Consent;
use App\Domain\Leads\Models\NewsletterSubscriber;
use App\Domain\Leads\Models\SuppressionEntry;
use App\Mail\NewsletterConfirmation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Dok. 07 §1/§8 — zapis do newslettera B2C w modelu DOUBLE OPT-IN.
 *
 * Rekord powstaje jako NIEPOTWIERDZONY (confirmed_at=null); dopiero
 * kliknięcie linku w mailu potwierdzającym (ConfirmNewsletter) czyni zgodę
 * skuteczną. Adres z globalnego rejestru sprzeciwów (S-CD, rezygnacje,
 * sprzeciwy) NIE dostaje żadnej wiadomości — twarda blokada §6.
 *
 * Zwraca: 'suppressed' | 'already_confirmed' | 'pending'.
 */
class SubscribeToNewsletter
{
    public function handle(string $email, string $locale, ?string $ip, ?string $userAgent): string
    {
        $email = mb_strtolower(trim($email));

        // twarda blokada — adres w rejestrze sprzeciwów: nic nie wysyłamy, nic nie tworzymy
        if (SuppressionEntry::isSuppressed($email)) {
            return 'suppressed';
        }

        $existing = NewsletterSubscriber::where('email', $email)->first();
        if ($existing && $existing->confirmed_at !== null && $existing->unsubscribed_at === null) {
            return 'already_confirmed';
        }

        $version = (string) config('evastone.newsletter.consent_version');
        $wording = config("evastone.newsletter.consent_text.{$locale}")
            ?? config('evastone.newsletter.consent_text.de');
        $ipHash = $ip !== null ? hash('sha256', $ip.config('evastone.ip_pepper')) : '';
        $token = Str::random(64);

        $subscriber = NewsletterSubscriber::updateOrCreate(
            ['email' => $email],
            [
                'locale' => $locale,
                'token' => $token,
                'confirmed_at' => null,
                'unsubscribed_at' => null,
                'consent_text_version' => $version,
                'ip_hash' => $ipHash,
            ],
        );

        // dziennik zgód — brzmienie z chwili wyrażenia, jeszcze niepotwierdzone (§6)
        Consent::create([
            'subject_type' => NewsletterSubscriber::class,
            'subject_id' => $subscriber->id,
            'type' => 'newsletter',
            'granted' => false,
            'granted_at' => null,
            'document_version' => $version,
            'evidence' => [
                'stage' => 'opt_in_requested',
                'wording' => $wording,
                'locale' => $locale,
                'ip_hash' => $ipHash,
                'user_agent' => $userAgent,
                'at' => now()->toIso8601String(),
            ],
        ]);

        Mail::to($email)->send(new NewsletterConfirmation($subscriber, $wording));

        return 'pending';
    }
}
