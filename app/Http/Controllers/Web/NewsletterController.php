<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Leads\Actions\ConfirmNewsletter;
use App\Domain\Leads\Actions\SubscribeToNewsletter;
use App\Domain\Leads\Actions\UnsubscribeNewsletter;
use App\Http\Controllers\Controller;
use App\Http\Requests\NewsletterSubscribeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dok. 07 — publiczne endpointy newslettera B2C (double opt-in).
 * Front (LP2/LP3) POST-uje zapis i pokazuje potwierdzenie inline; linki
 * confirm/unsubscribe trafiają do maila. Throttling w definicji tras.
 */
class NewsletterController extends Controller
{
    public function subscribe(NewsletterSubscribeRequest $request, SubscribeToNewsletter $action): JsonResponse|RedirectResponse
    {
        $status = $action->handle(
            email: $request->string('email')->toString(),
            locale: $request->string('locale')->toString(),
            ip: $request->ip(),
            userAgent: $request->userAgent(),
        );

        // odpowiedź neutralna: nie zdradzamy, czy adres jest w rejestrze sprzeciwów
        $confirmed = $status === 'already_confirmed';
        $message = $confirmed
            ? __('Ten adres jest już zapisany.')
            : __('Sprawdź skrzynkę — wysłaliśmy link potwierdzający zapis.');

        if ($request->expectsJson()) {
            return response()->json(['status' => $status, 'message' => $message]);
        }

        return back()->with('newsletter_status', $message);
    }

    public function confirm(string $token, ConfirmNewsletter $action): View
    {
        $subscriber = $action->handle($token);

        return view('newsletter.result', [
            'ok' => $subscriber !== null,
            'kind' => 'confirm',
            'locale' => $subscriber?->locale ?? app()->getLocale(),
        ]);
    }

    public function unsubscribe(string $token, UnsubscribeNewsletter $action): View
    {
        $subscriber = $action->handle($token);

        return view('newsletter.result', [
            'ok' => $subscriber !== null,
            'kind' => 'unsubscribe',
            'locale' => $subscriber?->locale ?? app()->getLocale(),
        ]);
    }
}
