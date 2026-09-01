<?php

declare(strict_types=1);

namespace App\Domain\Leads\Actions;

use App\Domain\Consents\Models\Consent;
use App\Domain\Leads\Models\NewsletterSubscriber;

/**
 * Dok. 07 — drugi krok double opt-in: potwierdzenie z linku w mailu.
 * Dopiero tutaj zgoda staje się skuteczna (granted=true w dzienniku).
 *
 * Zwraca subskrybenta albo null (token nieznany). Idempotentne: ponowne
 * kliknięcie nie duplikuje potwierdzenia.
 */
class ConfirmNewsletter
{
    public function handle(string $token): ?NewsletterSubscriber
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();
        if ($subscriber === null) {
            return null;
        }

        if ($subscriber->confirmed_at === null) {
            $subscriber->update([
                'confirmed_at' => now(),
                'unsubscribed_at' => null,
            ]);

            Consent::where('subject_type', NewsletterSubscriber::class)
                ->where('subject_id', $subscriber->id)
                ->where('type', 'newsletter')
                ->latest('id')
                ->first()?->update([
                    'granted' => true,
                    'granted_at' => now(),
                ]);
        }

        return $subscriber;
    }
}
