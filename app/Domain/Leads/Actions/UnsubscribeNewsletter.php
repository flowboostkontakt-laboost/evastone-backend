<?php

declare(strict_types=1);

namespace App\Domain\Leads\Actions;

use App\Domain\Consents\Models\Consent;
use App\Domain\Leads\Models\NewsletterSubscriber;
use App\Domain\Leads\Models\SuppressionEntry;

/**
 * Dok. 07 §5 — rezygnacja honorowana NATYCHMIAST i utrwalona w globalnym
 * rejestrze sprzeciwów (§6), żeby adres nie wrócił do żadnej wysyłki w
 * poprzek kanałów. Cofa też zgodę w dzienniku.
 *
 * Zwraca subskrybenta albo null (token nieznany). Idempotentne.
 */
class UnsubscribeNewsletter
{
    public function handle(string $token): ?NewsletterSubscriber
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();
        if ($subscriber === null) {
            return null;
        }

        if ($subscriber->unsubscribed_at === null) {
            $subscriber->update(['unsubscribed_at' => now()]);
        }

        SuppressionEntry::suppress($subscriber->email, 'unsubscribe', 'newsletter', 'opt-out z linku');

        Consent::where('subject_type', NewsletterSubscriber::class)
            ->where('subject_id', $subscriber->id)
            ->where('type', 'newsletter')
            ->whereNull('revoked_at')
            ->latest('id')
            ->first()?->update([
                'granted' => false,
                'revoked_at' => now(),
            ]);

        return $subscriber;
    }
}
