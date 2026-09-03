<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Leads\Models\Lead;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeadRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Lead B2B z /wholesale (spec 16 §4.1): walidacja serwerowa, honeypot,
 * blokada firm z PL i domen jednorazowych, webhook do CRM, redirect na danke.
 */
class LeadController extends Controller
{
    /** e-mailowe domeny jednorazowe — odrzucamy cicho (status=rejected). */
    private const DISPOSABLE = [
        'mailinator.com', 'guerrillamail.com', '10minutemail.com', 'tempmail.com',
        'temp-mail.org', 'yopmail.com', 'trashmail.com', 'getnada.com', 'sharklasers.com',
        'dispostable.com', 'fakeinbox.com', 'throwawaymail.com',
    ];

    public function store(LeadRequest $request, string $locale)
    {
        abort_unless(in_array($locale, config('evastone.locales'), true), 404);
        $data = $request->validated();

        $email = mb_strtolower(trim($data['email']));
        $domain = str_contains($email, '@') ? substr(strrchr($email, '@'), 1) : '';
        $country = strtoupper((string) ($data['land'] ?? ''));

        // reguły blokujące: firmy z PL (rynek B2B = DE/EN) + domeny jednorazowe
        $blocked = $country === 'PL' || in_array($domain, self::DISPOSABLE, true);

        $lead = Lead::create([
            'source' => $data['src'] ?? null,
            'locale' => $locale,
            'company_name' => $data['firma'],
            'contact_name' => $data['person'] ?? '',
            'email' => $email,
            'city' => $data['stadt'] ?? null,
            'country' => $country ?: null,
            'message' => null,
            'utm' => array_filter([
                'website' => $data['website'] ?? null,
                'wariant' => $data['wariant'] ?? null,
                'src' => $data['src'] ?? null,
            ]),
            'ip_hash' => $request->ip() ? hash('sha256', $request->ip().config('evastone.ip_pepper')) : '',
            'user_agent' => $request->userAgent(),
            'status' => $blocked ? 'rejected' : 'new',
        ]);

        if (! $blocked) {
            $this->notifyCrm($lead);
        }

        $slug = config("evastone.danke_slugs.{$locale}", 'danke');

        return redirect("/{$locale}/wholesale/{$slug}/");
    }

    public function danke(string $locale, string $danke): View
    {
        abort_unless(in_array($locale, config('evastone.locales'), true), 404);
        abort_unless($danke === config("evastone.danke_slugs.{$locale}"), 404);

        return view('leads.danke', ['locale' => $locale]);
    }

    /** Webhook do CRM (spec 16 §4.1). Bez URL-a → tylko log (do czasu integracji). */
    private function notifyCrm(Lead $lead): void
    {
        $url = config('evastone.lead_webhook_url');
        if (blank($url)) {
            Log::info('lead.new', ['id' => $lead->id, 'company' => $lead->company_name]);

            return;
        }

        try {
            Http::timeout(10)->retry(2, 500, throw: false)->post($url, [
                'id' => $lead->id, 'company_name' => $lead->company_name,
                'email' => $lead->email, 'city' => $lead->city, 'country' => $lead->country,
                'locale' => $lead->locale, 'source' => $lead->source, 'utm' => $lead->utm,
            ]);
        } catch (\Throwable $e) {
            Log::warning('lead.webhook_failed', ['id' => $lead->id, 'err' => $e->getMessage()]);
        }
    }
}
