<?php

declare(strict_types=1);

namespace App\Mail;

use App\Domain\Leads\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Dok. 07 §5 — mail double opt-in. Nadawca imienny (config, nigdy noreply@),
 * reply-to obsługiwany przez człowieka. Treść minimalna i neutralna:
 * bez kwot, rabatów, terminów. Ostateczne brzmienie DE/EN po native review.
 */
class NewsletterConfirmation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public NewsletterSubscriber $subscriber,
        public string $consentWording,
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            'de' => 'Bitte bestätigen Sie Ihre Newsletter-Anmeldung',
            'en' => 'Please confirm your newsletter subscription',
            'pl' => 'Potwierdź zapis do newslettera',
        ];
        $locale = $this->subscriber->locale;

        return new Envelope(
            from: new Address(
                (string) config('evastone.newsletter.from_address'),
                (string) config('evastone.newsletter.from_name'),
            ),
            replyTo: [new Address((string) config('evastone.newsletter.reply_to'))],
            subject: $subjects[$locale] ?? $subjects['de'],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.newsletter.confirm',
            with: [
                'locale' => $this->subscriber->locale,
                'confirmUrl' => route('newsletter.confirm', $this->subscriber->token),
                'wording' => $this->consentWording,
            ],
        );
    }
}
