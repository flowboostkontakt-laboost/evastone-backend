<?php

declare(strict_types=1);

namespace App\Domain\Import\Actions;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Collection;
use App\Domain\Catalog\Models\Cut;
use App\Domain\Catalog\Models\MediaTranslation;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\Stone;
use App\Domain\Import\Models\ImportProductRaw;
use Illuminate\Support\Facades\DB;

/**
 * §4 krok 7: publikacja falami per kategoria.
 *
 * Upsert po model_no (klucz biznesowy). source_hash = row_hash rekordu
 * staging — niezmieniony rekord jest pomijany (idempotencja).
 *
 * Status produktu:
 *  - bloker walidacji            → needs_review
 *  - komplet zatwierdzeń (3×approved) → published
 *  - w pozostałych przypadkach   → draft (czeka na akceptację klientki §7.3)
 */
class PublishBatch
{
    public function handle(?string $category = null, bool $dryRun = false, ?string $batchId = null): array
    {
        $mirror = rtrim(config('evastone.import.mirror_path'), '/');

        $stats = ['published' => 0, 'draft' => 0, 'needs_review' => 0, 'unchanged' => 0, 'skipped_unvalidated' => 0];

        $rows = ImportProductRaw::whereIn('state', ['normalized', 'imported'])
            ->when($batchId, fn ($q) => $q->where('batch_id', $batchId))
            ->orderBy('id')
            ->get()
            // przy wielu przebiegach liczy się najnowszy rekord danego produktu
            ->groupBy(fn ($r) => $r->normalized['model_no'] ?? $r->external_id)
            ->map(fn ($group) => $group->last());

        foreach ($rows as $row) {
            $n = $row->normalized;

            if ($category !== null && ($n['category_slug'] ?? null) !== $category) {
                continue;
            }

            if (! ($row->errors['validated'] ?? false)) {
                $stats['skipped_unvalidated']++;
                continue;
            }

            $existing = Product::withTrashed()->where('model_no', $n['model_no'])->first();
            if ($existing && $existing->source_hash === $row->row_hash && $row->state === 'imported') {
                // dane bez zmian — ale status publikacji przeliczamy zawsze,
                // bo akceptacja klientki (§7.3) mogła nadejść między przebiegami
                if (! $dryRun) {
                    $this->refreshStatus($existing, $row->errors['blockers'] ?? []);
                }
                $stats['unchanged']++;
                continue;
            }

            $blockers = $row->errors['blockers'] ?? [];

            if ($dryRun) {
                $stats[$blockers === [] ? 'draft' : 'needs_review']++;
                continue;
            }

            DB::transaction(function () use ($row, $n, $blockers, $mirror, &$stats) {
                $product = Product::withTrashed()->updateOrCreate(
                    ['model_no' => $n['model_no']],
                    [
                        'category_id' => Category::where('slug', $n['category_slug'])->value('id'),
                        'stone_id' => Stone::where('slug', $n['stone_slug'])->value('id'),
                        'cut_id' => Cut::where('slug', $n['cut_slug'])->value('id'),
                        'collection_id' => Collection::where('slug', $n['collection_slug'])->value('id'),
                        'spec' => $n['spec'],
                        'sizes' => $n['sizes'],
                        'source_hash' => $row->row_hash,
                    ],
                );

                foreach ($n['translations'] as $locale => $t) {
                    $product->translations()->updateOrCreate(
                        ['locale' => $locale],
                        [
                            'name' => $t['name'],
                            'slug_localized' => $t['slug_localized'],
                            'answer_summary' => $t['answer_summary'],
                            'description' => $t['description'],
                            'faq' => $t['faq'],
                            'meta_title' => $t['meta_title'],
                            'meta_description' => $t['meta_description'],
                            // treść z ComUp czeka na akceptację klientki (§3.2)
                            'review_status' => 'client_review',
                        ],
                    );
                }

                $this->attachImage($product, $n, $mirror);

                $status = $this->refreshStatus($product->refresh(), $blockers);

                $row->update(['state' => 'imported']);
                $stats[$status]++;
            });
        }

        return $stats;
    }

    /** Wylicza i zapisuje status publikacji; zwraca ustawiony status. */
    private function refreshStatus(Product $product, array $blockers): string
    {
        $product->load('translations');

        $status = match (true) {
            $blockers !== [] => 'needs_review',
            $product->isPublishable() => 'published',
            default => 'draft',
        };

        $product->update([
            'status' => $status,
            'published_at' => $status === 'published'
                ? ($product->published_at ?? now())
                : $product->published_at,
        ]);

        return $status;
    }

    /**
     * §6.1 — nazwa pliku {model_no}-{n}.{ext} (obrazkowe SEO), dedup po
     * istniejącej nazwie, konwersje w kolejce `media`.
     * §6.2 — alt-y per locale do media_translations.
     */
    private function attachImage(Product $product, array $n, string $mirror): void
    {
        $file = $n['image_file'] ?? null;
        $url = $n['image_url'] ?? null;

        if ($file !== null && is_file("{$mirror}/{$file}")) {
            $sourcePath = "{$mirror}/{$file}";
            $ext = 'webp';
        } elseif ($url !== null) {
            $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) ?: 'jpg';
            $sourcePath = null; // pobranie dopiero gdy media brakuje (idempotencja bez ruchu HTTP)
        } else {
            return;
        }

        $targetName = "{$n['model_no']}-1.{$ext}";

        $media = $product->getMedia('gallery')->firstWhere('file_name', $targetName);

        if ($media === null && $sourcePath === null && $url !== null) {
            $tmp = tempnam(sys_get_temp_dir(), 'evastone-img');
            $body = \Illuminate\Support\Facades\Http::timeout(30)->retry(3, 1000, throw: false)->get($url);
            if (! $body->successful()) {
                return; // walidacja miała URL — błąd pobrania zostawia produkt bez media (raport wychwyci)
            }
            file_put_contents($tmp, $body->body());
            $sourcePath = $tmp;
        }

        if ($media === null) {
            $adder = $product->addMedia($sourcePath);
            if ($file !== null) {
                $adder->preservingOriginal(); // plik w kopii lustrzanej zostaje; tmp z HTTP jest przenoszony
            }
            $media = $adder->usingFileName($targetName)->toMediaCollection('gallery');
        }

        foreach ($n['translations'] as $locale => $t) {
            if (filled($t['image_alt'] ?? null)) {
                MediaTranslation::updateOrCreate(
                    ['media_id' => $media->id, 'locale' => $locale],
                    ['alt' => $t['image_alt']],
                );
            }
        }
    }
}
