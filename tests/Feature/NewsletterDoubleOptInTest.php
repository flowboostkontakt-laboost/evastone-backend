<?php

declare(strict_types=1);

use App\Domain\Consents\Models\Consent;
use App\Domain\Leads\Actions\ConfirmNewsletter;
use App\Domain\Leads\Actions\SubscribeToNewsletter;
use App\Domain\Leads\Actions\UnsubscribeNewsletter;
use App\Domain\Leads\Models\NewsletterSubscriber;
use App\Domain\Leads\Models\SuppressionEntry;
use App\Mail\NewsletterConfirmation;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    config()->set('evastone.ip_pepper', 'test-pepper');
});

it('zapis tworzy niepotwierdzony rekord, loguje zgodę i wysyła mail (double opt-in)', function () {
    Mail::fake();

    $status = app(SubscribeToNewsletter::class)->handle('anna@example.com', 'de', '203.0.113.5', 'PHPUnit');

    expect($status)->toBe('pending');

    $sub = NewsletterSubscriber::where('email', 'anna@example.com')->firstOrFail();
    expect($sub->confirmed_at)->toBeNull()
        ->and($sub->consent_text_version)->toBe(config('evastone.newsletter.consent_version'))
        ->and($sub->ip_hash)->not->toBe('')                 // IP zahashowane, nigdy surowe
        ->and($sub->ip_hash)->not->toContain('203.0.113');

    $consent = Consent::where('subject_id', $sub->id)->where('type', 'newsletter')->firstOrFail();
    expect($consent->granted)->toBeFalse()
        ->and($consent->evidence['wording'])->not->toBeEmpty()   // brzmienie z chwili wyrażenia
        ->and($consent->evidence['stage'])->toBe('opt_in_requested');

    Mail::assertSent(NewsletterConfirmation::class, fn ($m) => $m->hasTo('anna@example.com'));
});

it('potwierdzenie czyni zgodę skuteczną', function () {
    Mail::fake();
    app(SubscribeToNewsletter::class)->handle('bob@example.com', 'en', '203.0.113.9', 'PHPUnit');
    $sub = NewsletterSubscriber::where('email', 'bob@example.com')->firstOrFail();

    $result = app(ConfirmNewsletter::class)->handle($sub->token);

    expect($result)->not->toBeNull()
        ->and($sub->fresh()->confirmed_at)->not->toBeNull();
    $consent = Consent::where('subject_id', $sub->id)->latest('id')->firstOrFail();
    expect($consent->granted)->toBeTrue()
        ->and($consent->granted_at)->not->toBeNull();
});

it('rezygnacja jest natychmiastowa, trafia do rejestru sprzeciwów i cofa zgodę', function () {
    Mail::fake();
    app(SubscribeToNewsletter::class)->handle('carla@example.com', 'pl', '203.0.113.1', 'PHPUnit');
    $sub = NewsletterSubscriber::where('email', 'carla@example.com')->firstOrFail();
    app(ConfirmNewsletter::class)->handle($sub->token);

    app(UnsubscribeNewsletter::class)->handle($sub->token);

    expect($sub->fresh()->unsubscribed_at)->not->toBeNull()
        ->and(SuppressionEntry::isSuppressed('carla@example.com'))->toBeTrue();
    $consent = Consent::where('subject_id', $sub->id)->latest('id')->firstOrFail();
    expect($consent->granted)->toBeFalse()
        ->and($consent->revoked_at)->not->toBeNull();
});

it('adres z rejestru sprzeciwów (S-CD) nie dostaje nic i nie zostaje zapisany', function () {
    Mail::fake();
    SuppressionEntry::suppress('blocked@example.com', 's_cd', 'all', 'płyta');

    $status = app(SubscribeToNewsletter::class)->handle('blocked@example.com', 'de', '203.0.113.2', 'PHPUnit');

    expect($status)->toBe('suppressed')
        ->and(NewsletterSubscriber::where('email', 'blocked@example.com')->exists())->toBeFalse();
    Mail::assertNothingSent();
});

it('po rezygnacji nie da się ponownie zapisać przez formularz (opt-out honorowany)', function () {
    Mail::fake();
    app(SubscribeToNewsletter::class)->handle('dora@example.com', 'de', '203.0.113.3', 'PHPUnit');
    $sub = NewsletterSubscriber::where('email', 'dora@example.com')->firstOrFail();
    app(UnsubscribeNewsletter::class)->handle($sub->token);

    Mail::fake(); // wyzeruj licznik
    $status = app(SubscribeToNewsletter::class)->handle('dora@example.com', 'de', '203.0.113.3', 'PHPUnit');

    expect($status)->toBe('suppressed');
    Mail::assertNothingSent();
});

it('endpoint zapisu wymaga świadomej zgody i odrzuca boty (honeypot)', function () {
    Mail::fake();

    $this->post('/newsletter/subscribe', ['email' => 'eva@example.com', 'locale' => 'de'])
        ->assertSessionHasErrors('consent');

    $this->post('/newsletter/subscribe', ['email' => 'bot@example.com', 'locale' => 'de', 'consent' => '1', 'website' => 'spam'])
        ->assertSessionHasErrors('website');

    Mail::assertNothingSent();
});

it('endpoint zapisu z poprawnymi danymi zwraca neutralny komunikat i wysyła mail', function () {
    Mail::fake();

    $this->postJson('/newsletter/subscribe', ['email' => 'frank@example.com', 'locale' => 'en', 'consent' => true])
        ->assertOk()
        ->assertJsonPath('status', 'pending');

    Mail::assertSent(NewsletterConfirmation::class);
});
