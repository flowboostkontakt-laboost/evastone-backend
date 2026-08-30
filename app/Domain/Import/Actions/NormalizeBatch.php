<?php

declare(strict_types=1);

namespace App\Domain\Import\Actions;

use App\Domain\Catalog\Models\Collection;
use App\Domain\Catalog\Models\Cut;
use App\Domain\Catalog\Models\Stone;
use App\Domain\Import\Models\ImportProductRaw;

/**
 * §4 krok 4: raw → znormalizowane mapowanie na słowniki.
 *
 * Payload zostaje surowy; wynik ląduje w kolumnie `normalized`.
 * Kamień/szlif spoza zamkniętej listy → state=rejected (kolejka ręczna,
 * NIE do bazy produktów) — §4.4.
 */
class NormalizeBatch
{
    /** stary segment URL kategorii (de) → slug techniczny słownika */
    private const CATEGORY_MAP = [
        'ringe' => 'pierscionki',
        'armreifen' => 'bransolety',
        'ohrringe' => 'kolczyki',
        'colliers' => 'kolie',
        'anhaenger' => 'zawieszki',
        'sets' => 'sety',
    ];

    public function handle(string $batchId): array
    {
        $stones = Stone::with('translations')->get();
        $cuts = Cut::with('translations')->get();
        $collections = Collection::with('translations')->get();

        $stats = ['normalized' => 0, 'rejected' => 0];

        $rows = ImportProductRaw::where('batch_id', $batchId)
            ->where('source', 'comup')
            ->where('state', 'raw')
            ->get();

        foreach ($rows as $row) {
            $p = $row->payload;
            $de = $p['locales']['de'] ?? [];
            $props = collect($de['jsonld_product']['additionalProperty'] ?? [])
                ->pluck('value', 'name');

            $blockers = [];

            $categorySlug = self::CATEGORY_MAP[$p['category_segment_de']] ?? null;
            if ($categorySlug === null) {
                $blockers[] = "kategoria spoza listy: {$p['category_segment_de']}";
            }

            $stone = $this->matchDictionary($stones, (string) $props->get('Edelstein', ''));
            if ($stone === null && $props->get('Edelstein')) {
                $blockers[] = 'kamień spoza listy 8: '.$props->get('Edelstein');
            }

            $cut = $this->matchDictionary($cuts, (string) $props->get('Schliff', ''));
            if ($cut === null && $props->get('Schliff')) {
                $blockers[] = 'szlif spoza listy 4: '.$props->get('Schliff');
            }

            $collection = $this->matchDictionary($collections, (string) $props->get('Kollektion', ''));

            // słownikowe odrzuty nie wchodzą do bazy produktów w ogóle (§4.4)
            if ($blockers !== []) {
                $row->update(['state' => 'rejected', 'errors' => ['blockers' => $blockers]]);
                $stats['rejected']++;
                continue;
            }

            $sizes = $this->parseSizes((string) $props->get('Größenrange', ''));

            $translations = [];
            foreach ($p['locales'] as $locale => $page) {
                $prod = $page['jsonld_product'] ?? [];
                $translations[$locale] = [
                    'name' => $prod['name'] ?? null,
                    'slug_localized' => $p['source_slug'],
                    'answer_summary' => $prod['description'] ?? null,
                    'description' => null, // ComUp nie miał rozwinięcia narracyjnego
                    'faq' => $page['jsonld_faq'] ?? [],
                    'meta_title' => $page['title'] ?? null,
                    'meta_description' => $page['meta_description'] ?? null,
                    'image_alt' => $page['image_alt'] ?? null,
                ];
            }

            $modelNo = $p['model_no'] ?? null;

            $normalized = [
                'model_no' => $modelNo,
                'category_slug' => $categorySlug,
                'stone_slug' => $stone?->slug,
                'cut_slug' => $cut?->slug,
                'collection_slug' => $collection?->slug,
                'finish' => $p['finish'],
                'sizes' => $sizes,
                'image_file' => $de['image_file'] ?? null,
                'spec' => array_filter([
                    'material' => $de['jsonld_product']['material'] ?? '925 Silber',
                    'finish' => $p['finish'],
                    'stone' => $stone?->slug,
                    'cut' => $cut?->slug,
                    'collection' => $collection?->slug,
                    'model_no' => $modelNo,
                    'sizes_available' => $sizes['values'] ?? null,
                    'workshop' => null,
                ], fn ($v) => $v !== null),
                'translations' => $translations,
            ];

            $row->update(['state' => 'normalized', 'normalized' => $normalized, 'errors' => null]);
            $stats['normalized']++;
        }

        return $stats;
    }

    /** Dopasowanie po nazwie DE w tłumaczeniach słownika (case-insensitive). */
    private function matchDictionary($models, string $nameDe): ?object
    {
        if ($nameDe === '') {
            return null;
        }

        return $models->first(
            fn ($m) => $m->translations->contains(
                fn ($t) => $t->locale === 'de' && mb_strtolower($t->name) === mb_strtolower($nameDe)
            )
        );
    }

    /** „15–23" / „17–21 cm" → struktura; pełna lista wartości dla zakresów bez jednostki. */
    private function parseSizes(string $range): ?array
    {
        if ($range === '') {
            return null;
        }

        if (! preg_match('/(\d+)\s*[–-]\s*(\d+)\s*(cm)?/u', $range, $m)) {
            return ['range' => $range];
        }

        $sizes = [
            'range' => $range,
            'min' => (int) $m[1],
            'max' => (int) $m[2],
            'unit' => $m[3] ?? null,
        ];

        if (($m[3] ?? null) === null || $m[3] === '') {
            $sizes['unit'] = null;
            $sizes['values'] = array_map('strval', range((int) $m[1], (int) $m[2]));
        }

        return $sizes;
    }
}
