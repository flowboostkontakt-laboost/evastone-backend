<?php

declare(strict_types=1);

namespace App\Domain\Import\Actions;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Stone;
use App\Domain\Import\Models\ImportProductRaw;
use Illuminate\Support\Str;

/**
 * §4 krok 4 dla źródła „comup-dump" (pełny katalog B2B ComUp).
 *
 * W odróżnieniu od „live" mamy tu PRAWDZIWE opisy (text z CKEditora),
 * nazwy serii, wykończenia i wymiary. Dlatego:
 *  - description = realny tekst z ComUp (lekko odkażony), do akceptacji
 *    klientki (review_status=client_review, jak w PublishBatch);
 *  - answer_summary / FAQ / alt-y nadal generujemy DETERMINISTYCZNIE
 *    z pól rzeczywistych (kontrakt AEO §4.4 wymaga 40–60 słów + 2–4 FAQ),
 *    bo opisy ComUp to tabelki spec, nie odpowiedzi w formacie AEO.
 *
 * Kamień spoza (rozszerzonego) słownika → NIE odrzucamy rekordu (decyzja
 * właściciela: import wszystkich 2928), tylko stone_slug=null + ostrzeżenie
 * do raportu. Kategoria zawsze mapuje się na istniejący slug (NOT NULL w DB).
 */
class NormalizeDumpBatch
{
    /** legacy products.category_id → slug słownika (patrz DictionarySeeder). */
    private const CATEGORY_ID_MAP = [
        2 => 'pierscionki',
        11 => 'kolczyki',
        3 => 'zawieszki',        // „Wisiory" = wisiorki/zawieszki
        15 => 'kolie', 16 => 'kolie',
        1 => 'bransolety', 12 => 'bransolety',
        13 => 'sety', 14 => 'sety',
        17 => 'unikaty',
        19 => 'klipsy', 22 => 'klipsy', 24 => 'klipsy', 25 => 'klipsy',
        27 => 'klipsy', 29 => 'klipsy', 32 => 'klipsy',
        20 => 'broszki', 21 => 'broszki', 23 => 'broszki', 34 => 'broszki',
        9 => 'pudelka', 10 => 'pudelka',
        8 => 'katalogi',
    ];

    private const FINISH_LABELS = [
        'de' => ['silber' => 'Silber', 'vergoldet' => 'Silber mit Vergoldung', 'oxidiert' => 'Silber oxidiert', 'kombi' => 'Silber mit Vergoldung und Oxidierung'],
        'en' => ['silber' => 'silver', 'vergoldet' => 'silver with gilding', 'oxidiert' => 'oxidised silver', 'kombi' => 'silver with gilding and oxidation'],
        'pl' => ['silber' => 'srebrne', 'vergoldet' => 'srebro z pozłotą', 'oxidiert' => 'srebro oksydowane', 'kombi' => 'srebro z pozłotą i oksydą'],
    ];

    public function handle(string $batchId): array
    {
        $stones = Stone::with('translations')->get();
        $categories = Category::with('translations')->get()->keyBy('slug');

        $stats = ['normalized' => 0, 'rejected' => 0];

        $rows = ImportProductRaw::where('batch_id', $batchId)
            ->where('source', 'comup-dump')
            ->whereIn('state', ['raw', 'rejected'])
            ->get();

        foreach ($rows as $row) {
            $p = $row->payload;
            $warnings = [];

            $modelNo = $p['model_no'];

            // --- kategoria (zawsze na istniejący slug) ----------------------
            $categorySlug = self::CATEGORY_ID_MAP[$p['category']['id']] ?? 'inne';
            if (! $categories->has($categorySlug)) {
                $categorySlug = 'inne';
            }
            if (($p['category']['id'] ?? 0) === 0 || ! isset(self::CATEGORY_ID_MAP[$p['category']['id']])) {
                $warnings[] = 'kategoria ComUp poza mapą ('.($p['category']['names']['pl'] ?? 'brak').') → inne';
            }

            // --- kamień (rozszerzony słownik + aliasy) -----------------------
            $stone = null;
            $stonesRaw = $p['stones_raw'] ?? [];
            if ($stonesRaw !== []) {
                foreach ($stonesRaw as $raw) {
                    [$candidate, $viaAlias] = $this->matchStone($stones, $raw);
                    if ($candidate !== null) {
                        $stone = $candidate;
                        if ($viaAlias) {
                            $warnings[] = "kamień zmapowany aliasem: {$raw} → {$stone->slug}";
                        }
                        break;
                    }
                }
                if ($stone === null) {
                    $warnings[] = 'kamień spoza słownika: '.implode(', ', $stonesRaw).' → pominięto';
                } elseif (count($stonesRaw) > 1) {
                    $warnings[] = 'wiele kamieni ('.implode(', ', $stonesRaw).") → przyjęto {$stone->slug}";
                }
            }

            // --- wykończenie z „shape" ComUp --------------------------------
            $finish = $this->finishFromShape($p['finish_shape']['pl'] ?? '');

            // --- seria: WYŁĄCZNIE nazwana seria z FK (WILD BULL, CHINESE DRAGON…).
            // subname w ComUp jest niepewny (często powtarza kamień/kolor), więc
            // go nie używamy jako serii. Zachowana w spec; kolekcje zamknięte → null.
            $series = $p['series']['pl'] ?? null;
            $series = filled($series) ? trim($series) : null;

            // --- treści per locale ------------------------------------------
            $translations = [];
            foreach (['de', 'en', 'pl'] as $locale) {
                $catName = $categories[$categorySlug]->translation($locale)?->name ?? $categorySlug;
                $stoneName = $stone?->translation($locale)?->name;
                $realText = $this->sanitize($p['locales'][$locale]['text'] ?? '');
                $realAlt = $p['locales'][$locale]['alt'] ?? '';

                $translations[$locale] = $this->buildTranslation(
                    $locale, $modelNo, $catName, $stoneName, $finish, $series, $realText, $realAlt,
                );
            }

            $normalized = [
                'model_no' => $modelNo,
                'category_slug' => $categorySlug,
                'stone_slug' => $stone?->slug,
                'cut_slug' => null,          // szlif nie występuje w ComUp
                'collection_slug' => null,   // serie ComUp poza zamkniętą listą kolekcji → spec
                'finish' => $finish,
                'sizes' => null,             // rozmiarówka dojdzie z Comarcha (§5)
                // zdjęcie profilowe publicznie serwowane przez stary serwer ComUp
                // (statyczny plik, bez logowania). PublishBatch pobierze i podłączy.
                'image_url' => $this->imageUrl($p),
                'image_file' => null,
                'spec' => array_filter([
                    'material' => '925 Silber',
                    'finish' => $finish,
                    'stone' => $stone?->slug,
                    'stones_raw' => $stonesRaw !== [] ? $stonesRaw : null,
                    'series' => $series,
                    'model_no' => $modelNo,
                    'width' => $p['width'] ?? null,
                    'weight' => $p['weight'] ?? null,
                    'comup_id' => $p['comup_id'] ?? null,
                    'legacy_profile_img' => $p['image']['profile_img'] ?? null,
                    'legacy_gallery_count' => count($p['image']['gallery'] ?? []),
                    'active_in_comup' => $p['active'] ?? null,
                    'workshop' => null,
                ], fn ($v) => $v !== null),
                'translations' => $translations,
            ];

            $row->update([
                'state' => 'normalized',
                'normalized' => $normalized,
                'errors' => $warnings === [] ? null : ['warnings' => $warnings],
            ]);
            $stats['normalized']++;
        }

        return $stats;
    }

    /**
     * URL zdjęcia na starym serwerze ComUp (publiczny plik statyczny):
     *  - profilowe: /upload/product/{id}/profile_img/{profile_img}.jpg
     *    (stara apka konwertowała png→jpg, próbka 30/30 to .jpg),
     *  - fallback: pierwsze zdjęcie galerii /upload/product/{id}/gallery/{photo_id}/{plik}.
     */
    private function imageUrl(array $p): ?string
    {
        $base = rtrim((string) config('evastone.import.comup_upload_base'), '/');
        $id = $p['comup_id'] ?? null;
        if ($base === '' || $id === null) {
            return null;
        }

        $profile = $p['image']['profile_img'] ?? null;
        if (filled($profile)) {
            return "{$base}/upload/product/{$id}/profile_img/{$profile}.jpg";
        }

        $gallery = $p['image']['gallery'][0] ?? null;
        if ($gallery !== null) {
            return "{$base}/upload/product/{$id}/gallery/{$gallery['photo_id']}/{$gallery['file']}";
        }

        return null;
    }

    /** @return array{0: ?object, 1: bool} [kamień, czy przez alias] */
    private function matchStone($stones, string $raw): array
    {
        $needle = mb_strtolower(trim($raw));
        if ($needle === '') {
            return [null, false];
        }

        $exact = $stones->first(
            fn ($s) => $s->translations->contains(fn ($t) => mb_strtolower($t->name) === $needle),
        );
        if ($exact !== null) {
            return [$exact, false];
        }

        foreach (config('evastone.stone_aliases', []) as $slug => $aliases) {
            if (in_array($needle, array_map('mb_strtolower', $aliases), true)) {
                return [$stones->firstWhere('slug', $slug), true];
            }
        }

        return [null, false];
    }

    /** Nazwa „shape" ComUp (srebrny/pozłocony/oksydowany…) → wykończenie kanoniczne. */
    private function finishFromShape(string $shape): string
    {
        $s = mb_strtolower($shape);
        $gilded = str_contains($s, 'złot') || str_contains($s, 'zlot')
            || str_contains($s, 'pozłoc') || str_contains($s, 'pozloc')
            || str_contains($s, 'gold');
        $oxidised = str_contains($s, 'oksyd') || str_contains($s, 'oxyd') || str_contains($s, 'oxid');

        return match (true) {
            $gilded && $oxidised => 'kombi',
            $gilded => 'vergoldet',
            $oxidised => 'oxidiert',
            default => 'silber', // srebrny oraz „ruten" (ciemne wykończenie bez złota/oksydy) — patrz warning
        };
    }

    /**
     * Odkażenie HTML z CKEditora:
     *  - usuwa skrypty/style oraz atrybuty zdarzeń (on...) i javascript:,
     *  - USUWA ceny (§3.3/§4.4 — cena w treści to blok absolutny): wiersze
     *    tabeli <tr> z kwotą w walucie lecą w całości, a resztkowe tokeny
     *    ceny są wycinane. ComUp bywa dwujęzyczny w cenach (zł/PLN/€/EUR).
     */
    private function sanitize(string $html): ?string
    {
        $html = trim($html);
        if ($html === '') {
            return null;
        }

        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $html = preg_replace('#\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html) ?? $html;
        $html = preg_replace('#\shref\s*=\s*("javascript:[^"]*"|\'javascript:[^\']*\')#i', '', $html) ?? $html;

        $priceToken = '\d+[\s,.]?\d*\s*(?:zł|PLN|€|EUR)';
        // 1) całe wiersze tabeli zawierające cenę (typowy „Cena: 120 zł")
        $html = preg_replace('#<tr\b[^>]*>(?:(?!</tr>).)*?'.$priceToken.'.*?</tr>#isu', '', $html) ?? $html;
        // 2) resztkowe tokeny ceny poza tabelą
        $html = preg_replace('#'.$priceToken.'#iu', '', $html) ?? $html;

        return trim($html) ?: null;
    }

    /**
     * Treści per locale: realny opis + deterministyczne answer_summary/FAQ/alt.
     */
    private function buildTranslation(
        string $locale,
        string $modelNo,
        string $catName,
        ?string $stoneName,
        string $finish,
        ?string $series,
        ?string $description,
        string $realAlt,
    ): array {
        $finishLabel = self::FINISH_LABELS[$locale][$finish];
        $name = "{$catName} {$modelNo}";
        $seriesClause = $this->seriesClause($locale, $series);

        [$summary, $faq, $metaTitle, $altFallback] = match ($locale) {
            'de' => [
                "{$catName} {$modelNo} aus 925 Silber, Ausführung {$finishLabel}."
                .($stoneName ? " Der Stein ist {$stoneName}." : ' Das Stück kommt ohne Stein aus.')
                .$seriesClause
                .' Die Oberflächenstruktur entsteht in der eigenen Goldschmiedewerkstatt und wiederholt sich in keinem zweiten Stück.'
                ." Verfügbarkeit erfragen Sie in Ihrer Boutique unter der Modellnummer {$modelNo}."
                .' Jedes Stück wird einzeln gefertigt und geprüft.',
                array_values(array_filter([
                    ['q' => 'Wie frage ich nach diesem Modell?', 'a' => "Nennen Sie in der Boutique die Modellnummer {$modelNo}. Wir verkaufen nicht direkt an Endkundinnen — die Verfügbarkeit prüft Ihre Verkaufsstelle."],
                    ['q' => 'Wo kann ich das Stück kaufen?', 'a' => 'EvaStone verkauft über unabhängige Boutiquen und Juweliere in Europa. Die Verkaufsstellen finden Sie auf der Händlerkarte.'],
                    $stoneName ? ['q' => 'Welcher Stein ist verarbeitet?', 'a' => "Der Stein ist {$stoneName}. Jeder Stein wird mit Namen angegeben."] : null,
                ])),
                "{$catName} {$modelNo} — 925 Silber | EvaStone",
                "{$catName} {$modelNo} aus 925 Silber".($stoneName ? " mit {$stoneName}" : '').', Oberflächenstruktur EvaStone',
            ],
            'en' => [
                "{$catName} {$modelNo} in 925 sterling silver, {$finishLabel} finish."
                .($stoneName ? " The stone is {$stoneName}." : ' This piece is designed without a stone.')
                .$seriesClause
                .' The surface structure is created in our own goldsmith workshop and never repeats in any second piece.'
                ." Please ask your local boutique for availability, quoting model number {$modelNo}."
                .' Every piece is made and inspected individually.',
                array_values(array_filter([
                    ['q' => 'How do I ask for this model?', 'a' => "Quote model number {$modelNo} at the boutique. We do not sell directly to end customers — your store checks availability."],
                    ['q' => 'Where can I buy this piece?', 'a' => 'EvaStone sells through independent boutiques and jewellers across Europe. You will find stockists on the retailer map.'],
                    $stoneName ? ['q' => 'Which stone is set in this piece?', 'a' => "The stone is {$stoneName}. Every stone is named explicitly."] : null,
                ])),
                "{$catName} {$modelNo} — 925 sterling silver | EvaStone",
                "{$catName} {$modelNo} in 925 sterling silver".($stoneName ? " with {$stoneName}" : '').', EvaStone surface structure',
            ],
            default => [
                "{$catName} {$modelNo} ze srebra próby 925, wykończenie: {$finishLabel}."
                .($stoneName ? " Kamień to {$stoneName}." : ' Egzemplarz zaprojektowany bez kamienia.')
                .$seriesClause
                .' Struktura powierzchni powstaje w naszej pracowni złotniczej i nie powtarza się w żadnym drugim egzemplarzu.'
                ." O dostępność zapytaj w butiku, podając numer modelu {$modelNo}."
                .' Każdy egzemplarz wykonujemy i sprawdzamy osobno.',
                array_values(array_filter([
                    ['q' => 'Jak zapytać o ten model?', 'a' => "Podaj w butiku numer modelu {$modelNo}. Nie prowadzimy sprzedaży bezpośredniej — dostępność sprawdza punkt sprzedaży."],
                    ['q' => 'Gdzie kupię ten egzemplarz?', 'a' => 'EvaStone sprzedaje przez niezależne butiki i salony jubilerskie w Europie. Punkty sprzedaży znajdziesz na mapie dystrybutorów.'],
                    $stoneName ? ['q' => 'Jaki kamień jest w tym egzemplarzu?', 'a' => "Kamień to {$stoneName}. Każdy kamień podajemy z nazwy."] : null,
                ])),
                "{$catName} {$modelNo} — srebro próby 925 | EvaStone",
                "{$catName} {$modelNo} ze srebra próby 925".($stoneName ? " z kamieniem {$stoneName}" : '').', struktura powierzchni EvaStone',
            ],
        };

        return [
            'name' => $name,
            'slug_localized' => Str::slug($modelNo),
            'answer_summary' => $summary,
            'description' => $description,
            'faq' => $faq,
            'meta_title' => $metaTitle,
            'meta_description' => mb_substr($summary, 0, 160),
            'image_alt' => filled($realAlt) ? $realAlt : $altFallback,
        ];
    }

    private function seriesClause(string $locale, ?string $series): string
    {
        if (! filled($series)) {
            return '';
        }

        return match ($locale) {
            'de' => " Teil der Serie {$series}.",
            'en' => " Part of the {$series} series.",
            default => " Część serii {$series}.",
        };
    }
}
