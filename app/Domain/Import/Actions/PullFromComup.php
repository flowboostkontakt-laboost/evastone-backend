<?php

declare(strict_types=1);

namespace App\Domain\Import\Actions;

use App\Domain\Import\Models\ImportProductRaw;
use Illuminate\Support\Facades\File;

/**
 * §4 krok 3: dump ComUp → import_products_raw.
 *
 * Źródłem jest pełna kopia starej strony (repo ComUp zrzucone do statycznych
 * plików, mount /mirror). Payload = surowy rekord per produkt: JSON-LD,
 * meta, alt-y i wykończenie — BEZ czyszczenia (§4.2). Czyszczenie robi
 * dopiero import:normalize.
 *
 * Idempotencja: row_hash = sha256 z posortowanego payloadu; unikat
 * (source, row_hash) — ponowny przebieg na niezmienionych danych niczego
 * nie dodaje.
 */
class PullFromComup
{
    /** locale => pierwszy segment katalogu w starym URL-u */
    private const SECTIONS = ['de' => 'schmuck', 'en' => 'jewellery', 'pl' => 'bizuteria'];

    public function handle(string $batchId): array
    {
        $mirror = rtrim(config('evastone.import.mirror_path'), '/');

        if (! is_dir("{$mirror}/de")) {
            throw new \RuntimeException("Kopia ComUp niedostępna pod: {$mirror}");
        }

        $finishByNumber = $this->finishMap($mirror);

        $stats = ['found' => 0, 'inserted' => 0, 'unchanged' => 0];

        foreach (glob("{$mirror}/de/schmuck/*/*/index.html") as $dePath) {
            $slug = basename(dirname($dePath));
            $categorySegmentDe = basename(dirname($dePath, 2));

            $payload = [
                'source_slug' => $slug,
                'category_segment_de' => $categorySegmentDe,
                'finish' => $finishByNumber[$this->numberFromSlug($slug)] ?? null,
                'locales' => [],
            ];

            foreach (self::SECTIONS as $locale => $section) {
                $paths = glob("{$mirror}/{$locale}/{$section}/*/{$slug}/index.html");
                if ($paths === []) {
                    continue;
                }
                $payload['locales'][$locale] = $this->extractPage($paths[0], $mirror);
            }

            $payload['model_no'] = $payload['locales']['de']['jsonld_product']['sku'] ?? null;
            $stats['found']++;

            $rowHash = hash('sha256', json_encode($this->ksortDeep($payload), JSON_UNESCAPED_UNICODE));

            $existing = ImportProductRaw::where('source', 'comup')->where('row_hash', $rowHash)->first();
            if ($existing) {
                $stats['unchanged']++;
                continue;
            }

            ImportProductRaw::create([
                'batch_id' => $batchId,
                'source' => 'comup',
                'external_id' => $payload['model_no'] ?? $slug,
                'payload' => $payload,
                'row_hash' => $rowHash,
                'state' => 'raw',
            ]);
            $stats['inserted']++;
        }

        return $stats;
    }

    /** Wyciąga surowe dane z jednej karty produktu (jeden locale). */
    private function extractPage(string $path, string $mirror): array
    {
        $html = File::get($path);

        $jsonld = [];
        if (preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $m)) {
            $jsonld = json_decode($m[1], true) ?? [];
        }
        $graph = $jsonld['@graph'] ?? [$jsonld];

        $product = collect($graph)->firstWhere('@type', 'Product') ?? [];
        $faqPage = collect($graph)->firstWhere('@type', 'FAQPage') ?? [];

        preg_match('/<title>(.*?)<\/title>/s', $html, $mTitle);
        preg_match('/<meta name="description" content="([^"]*)"/', $html, $mDesc);

        $imageFile = null;
        if (isset($product['image']) && preg_match('#(img/photo/[^"\s]+)#', (string) $product['image'], $mi)) {
            $imageFile = preg_replace('/-\d+\.(webp|avif)$/', '-1080.webp', $mi[1]);
        }

        $imageAlt = null;
        if (preg_match('/<img[^>]*produkt-[^>]*alt="([^"]*)"/s', $html, $ma)) {
            $imageAlt = html_entity_decode($ma[1], ENT_QUOTES | ENT_HTML5);
        }

        return [
            'path' => str_replace('\\', '/', substr($path, strlen($mirror) + 1)),
            'title' => html_entity_decode(trim($mTitle[1] ?? ''), ENT_QUOTES | ENT_HTML5),
            'meta_description' => html_entity_decode($mDesc[1] ?? '', ENT_QUOTES | ENT_HTML5),
            'jsonld_product' => $product,
            'jsonld_faq' => collect($faqPage['mainEntity'] ?? [])
                ->map(fn (array $q) => [
                    'q' => $q['name'] ?? '',
                    'a' => $q['acceptedAnswer']['text'] ?? '',
                ])->all(),
            'image_file' => $imageFile,
            'image_alt' => $imageAlt,
        ];
    }

    /**
     * Mapa model-number → wykończenie z listingów kategorii
     * (atrybut data-wykonczenie na kartach w ComUp).
     */
    private function finishMap(string $mirror): array
    {
        $map = [];
        foreach (glob("{$mirror}/de/schmuck/*/index.html") as $listing) {
            $html = File::get($listing);
            preg_match_all(
                '/data-wykonczenie="([^"]+)".*?produkt-(\d+)-/s',
                $html,
                $matches,
                PREG_SET_ORDER,
            );
            foreach ($matches as $m) {
                $map[$m[2]] = $m[1];
            }
        }

        return $map;
    }

    private function numberFromSlug(string $slug): string
    {
        return explode('-', $slug)[0];
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
