<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Actions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * §5 — sync słownika Comarch: read-only, źródło nadrzędne dla model_no
 * i rozmiarówki. Cron co 6 h, timeout 30 s, retry 3× z backoffem.
 *
 * Przy niedostępności API: log + koniec — katalog działa na ostatnim
 * znanym stanie. Rozbieżność → comarch_discrepancies, nie ciche nadpisanie.
 */
class SyncComarchDictionary
{
    public function handle(): array
    {
        $baseUrl = config('comarch.base_url');

        if (blank($baseUrl)) {
            Log::warning('comarch:sync pominięty — brak COMARCH_BASE_URL (środowisko bez dostępu do ERP).');

            return ['status' => 'skipped', 'reason' => 'no_credentials'];
        }

        $response = Cache::remember(
            'comarch:dictionary',
            now()->addHours(config('comarch.cache_ttl_hours')),
            fn () => Http::withToken(config('comarch.token'))
                ->timeout(config('comarch.timeout'))
                ->retry(config('comarch.retries'), backoff: true)
                ->get("{$baseUrl}/dictionary/products")
                ->throw()
                ->json(),
        );

        // TODO(Blok 3): mapowanie odpowiedzi ERP → weryfikacja model_no,
        // rozmiarówki; rozbieżności do comarch_discrepancies (wygrywa Comarch).
        return ['status' => 'ok', 'records' => count($response ?? [])];
    }
}
