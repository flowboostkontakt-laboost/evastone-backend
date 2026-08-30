<?php

declare(strict_types=1);

namespace App\Domain\Import\Actions;

use App\Domain\Import\Models\ImportProductRaw;

/**
 * §4.4 — reguły blokujące publikację. Rekord z blokerem trafi do bazy
 * jako needs_review (widoczny w Filament), ale nie zostanie opublikowany.
 */
class ValidateBatch
{
    public function handle(string $batchId): array
    {
        $mirror = rtrim(config('evastone.import.mirror_path'), '/');
        $priceRegex = config('evastone.import.price_regex');
        $blacklist = config('evastone.import.blacklist', []);
        $locales = config('evastone.locales');

        $stats = ['valid' => 0, 'blocked' => 0];

        $rows = ImportProductRaw::where('batch_id', $batchId)
            ->where('state', 'normalized')
            ->get();

        foreach ($rows as $row) {
            $n = $row->normalized;
            $blockers = [];
            $warnings = [];

            if (blank($n['model_no'] ?? null)) {
                $blockers[] = 'brak model_no';
            }

            $imageFile = $n['image_file'] ?? null;
            $imageUrl = $n['image_url'] ?? null;
            if (blank($imageFile) && blank($imageUrl)) {
                $blockers[] = 'brak zdjęcia';
            } elseif (filled($imageFile) && ! is_file("{$mirror}/{$imageFile}")) {
                $blockers[] = "plik zdjęcia nie istnieje: {$imageFile}";
            }

            foreach ($locales as $locale) {
                $t = $n['translations'][$locale] ?? null;

                if ($t === null) {
                    $blockers[] = "brak tłumaczenia: {$locale}";
                    continue;
                }

                if (blank($t['answer_summary'] ?? null)) {
                    $blockers[] = "brak answer_summary: {$locale}";
                } else {
                    $words = count(preg_split('/\s+/u', trim($t['answer_summary']), -1, PREG_SPLIT_NO_EMPTY));
                    if ($words < 40 || $words > 60) {
                        $warnings[] = "answer_summary {$locale}: {$words} słów (cel 40–60)";
                    }
                }

                // §4.4 / §3.3 — cena w treści = blok absolutny
                $texts = implode(' ', array_filter([
                    $t['answer_summary'] ?? '',
                    $t['description'] ?? '',
                    $t['meta_description'] ?? '',
                    ...array_map(fn ($f) => ($f['q'] ?? '').' '.($f['a'] ?? ''), $t['faq'] ?? []),
                ]));

                if (preg_match($priceRegex, $texts)) {
                    $blockers[] = "cena w treści opisu: {$locale}";
                }

                foreach ($blacklist as $word) {
                    if (mb_stripos($texts, $word) !== false) {
                        $blockers[] = "słowo z czarnej listy [{$word}]: {$locale}";
                    }
                }

                $faqCount = count($t['faq'] ?? []);
                if ($faqCount < 2 || $faqCount > 4) {
                    $warnings[] = "FAQ {$locale}: {$faqCount} par (cel 2–4)";
                }

                if (blank($t['image_alt'] ?? null)) {
                    $blockers[] = "brak alt zdjęcia: {$locale}"; // §6.2
                }
            }

            $row->update(['errors' => [
                'validated' => true,
                'blockers' => $blockers,
                'warnings' => $warnings,
            ]]);

            $blockers === [] ? $stats['valid']++ : $stats['blocked']++;
        }

        return $stats;
    }
}
