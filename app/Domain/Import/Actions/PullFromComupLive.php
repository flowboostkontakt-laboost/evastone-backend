<?php

declare(strict_types=1);

namespace App\Domain\Import\Actions;

use App\Domain\Import\Models\ImportProductRaw;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

/**
 * §4 krok 3 — źródło „comup-live": publiczny katalog starej strony
 * (evastone.eu, platforma ComUp). Ścieżka crawlera z dok. 21 §1
 * (symfony/dom-crawler) — używana, bo pełny katalog (~3000 pozycji)
 * siedzi za loginem dystrybutora; publicznie widoczne jest ~550 modeli.
 *
 * Payload = surowy rekord per model (unia z 3 wersji językowych),
 * BEZ czyszczenia. row_hash idempotentnie jak przy źródle mirror.
 */
class PullFromComupLive
{
    private const BASE = 'https://evastone.eu';

    /** locale => [slug kategorii w ComUp => slug techniczny słownika] */
    private const CATEGORIES = [
        'pl' => [
            'pierscionki' => 'pierscionki', 'bransoletki' => 'bransolety',
            'kolczyki' => 'kolczyki', 'kolie' => 'kolie',
            'wisiorki' => 'zawieszki', 'sety' => 'sety',
        ],
        'de' => [
            'ringe' => 'pierscionki', 'armbander' => 'bransolety',
            'ohrringe' => 'kolczyki', 'kolie' => 'kolie',
            'anhanger' => 'zawieszki', 'sets' => 'sety',
        ],
        'en' => [
            'rings' => 'pierscionki', 'bracelets' => 'bransolety',
            'earrings' => 'kolczyki', 'necklaces' => 'kolie',
            'pendants' => 'zawieszki', 'sets' => 'sety',
        ],
    ];

    /** przerwa między żądaniami — grzeczny crawl cudzego hostingu */
    private const THROTTLE_US = 150_000;

    public function handle(string $batchId, ?int $limit = null): array
    {
        $stats = ['found' => 0, 'inserted' => 0, 'unchanged' => 0, 'fetch_errors' => 0];

        // 1. listingi kategorii → mapa model => [locale => tech slug kategorii]
        $models = [];
        foreach (self::CATEGORIES as $locale => $slugMap) {
            foreach ($slugMap as $comupSlug => $techSlug) {
                $html = $this->get("/{$locale}/gallery/category?slug={$comupSlug}");
                if ($html === null) {
                    $stats['fetch_errors']++;
                    continue;
                }
                preg_match_all(
                    '#href="'.self::BASE."/{$locale}/gallery/([A-Za-z0-9_-]+)\"#",
                    $html,
                    $m,
                );
                foreach (array_unique($m[1]) as $model) {
                    if ($model === 'category') {
                        continue;
                    }
                    // PL linkuje małymi literami, DE/EN wielkimi — unia po uppercase
                    $models[mb_strtoupper($model)]['categories'][$locale] = $techSlug;
                }
            }
        }

        if ($limit !== null) {
            $models = array_slice($models, 0, $limit, preserve_keys: true);
        }

        // 2. karty produktów per locale
        foreach ($models as $model => $meta) {
            $payload = [
                'model_no' => $model,
                'categories' => $meta['categories'],
                'locales' => [],
            ];

            foreach (array_keys(self::CATEGORIES) as $locale) {
                $html = $this->get("/{$locale}/gallery/{$model}");
                if ($html === null) {
                    $stats['fetch_errors']++;
                    continue;
                }
                $payload['locales'][$locale] = $this->extractPage($html);
            }

            // kanoniczny zapis modelu z H1 karty (URL bywa lowercase)
            $h1 = collect($payload['locales'])->pluck('h1')->filter()->first();
            if (filled($h1)) {
                $payload['model_no'] = $h1;
            }

            $stats['found']++;

            $rowHash = hash('sha256', json_encode($this->ksortDeep($payload), JSON_UNESCAPED_UNICODE));

            if (ImportProductRaw::where('source', 'comup-live')->where('row_hash', $rowHash)->exists()) {
                $stats['unchanged']++;
                continue;
            }

            ImportProductRaw::create([
                'batch_id' => $batchId,
                'source' => 'comup-live',
                'external_id' => $model,
                'payload' => $payload,
                'row_hash' => $rowHash,
                'state' => 'raw',
            ]);
            $stats['inserted']++;
        }

        return $stats;
    }

    /** Surowe pola z karty: h1, tabela spec (etykieta => wartość), zdjęcie profilowe. */
    private function extractPage(string $html): array
    {
        $crawler = new Crawler($html);

        $spec = [];
        $crawler->filter('table tr')->each(function (Crawler $tr) use (&$spec): void {
            $cells = $tr->filter('td');
            if ($cells->count() === 2) {
                $spec[trim($cells->eq(0)->text())] = trim($cells->eq(1)->text());
            }
        });

        $image = null;
        if (preg_match('#(https://evastone\.eu/upload/gallery/\d+/profile_img/original\.[a-z]+)#', $html, $m)) {
            // pierwszy upload/gallery w treści strony to zdjęcie bieżącego produktu
            $mainNode = $crawler->filter('img[src*="upload/gallery"]');
            $image = $mainNode->count() > 0 ? $mainNode->first()->attr('src') : $m[1];
        }

        return [
            'h1' => trim(optional($crawler->filter('h1')->first())->text('') ?? ''),
            'spec_table' => $spec,
            'image_url' => $image,
        ];
    }

    private function get(string $path): ?string
    {
        usleep(self::THROTTLE_US);

        try {
            $response = Http::timeout(30)
                ->retry(3, 1000, throw: false)
                ->withHeaders(['User-Agent' => 'EvaStone-Migration/1.0 (migracja katalogu na zlecenie wlasciciela)'])
                ->get(self::BASE.$path);

            return $response->successful() ? $response->body() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function ksortDeep(array $arr): array
    {
        ksort($arr);
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $arr[$k] = $this->ksortDeep($v);
            }
        }

        return $arr;
    }
}
