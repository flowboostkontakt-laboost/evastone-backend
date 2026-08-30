<?php

declare(strict_types=1);

namespace App\Domain\Import\Actions;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Stone;
use App\Domain\Import\Models\ImportProductRaw;

/**
 * §4 krok 4 dla źródła „comup-live".
 *
 * Stary ComUp ma tylko: nr katalogowy, materiał, kolor, kamień i zdjęcie.
 * Braki względem kontraktu AEO (answer_summary 40–60 słów, FAQ 2–4, alt-y)
 * uzupełniamy DETERMINISTYCZNIE z pól rzeczywistych — te same formuły,
 * którymi zbudowano treści prototypu. Zero zmyślonych faktów; szlif i
 * kolekcja nie występują w źródle → null.
 *
 * Kamień spoza zamkniętej listy 8 → state=rejected (kolejka ręczna §4.4).
 */
class NormalizeLiveBatch
{
    private const SPEC_LABELS = [
        'material' => ['MATERIAŁ', 'MATERIAL'],
        'color' => ['KOLOR', 'COLOR', 'FARBE'],
        'stone' => ['KAMIEŃ', 'STONE', 'STEIN'],
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
            ->where('source', 'comup-live')
            // rejected wraca do gry po zmianie słownika/aliasów — idempotentnie
            ->whereIn('state', ['raw', 'rejected'])
            ->get();

        foreach ($rows as $row) {
            $p = $row->payload;
            $modelNo = $p['model_no'];

            // kategoria: wartość z listingu; przy rozjeździe między locale
            // decyduje większość, rozjazd idzie do warnings (raport §4.5)
            $catVotes = array_count_values($p['categories'] ?? []);
            arsort($catVotes);
            $categorySlug = array_key_first($catVotes) ?? null;
            $warnings = [];
            if (count($catVotes) > 1) {
                $warnings[] = 'kategoria niespójna między wersjami językowymi: '
                    .json_encode($p['categories'], JSON_UNESCAPED_UNICODE);
            }

            if ($categorySlug === null || ! $categories->has($categorySlug)) {
                $row->update(['state' => 'rejected', 'errors' => ['blockers' => ['brak kategorii w listingach']]]);
                $stats['rejected']++;
                continue;
            }

            $specRaw = $this->mergedSpec($p['locales'] ?? []);
            $stoneRaw = $specRaw['stone'] ?? null;

            $stone = null;
            if (filled($stoneRaw)) {
                [$stone, $viaAlias] = $this->matchStone($stones, $stoneRaw);

                // wpis wielokamieniowy („Rubin, biały szafir i niebieski szafir")
                // → pierwszy rozpoznany kamień jako główny + ostrzeżenie
                if ($stone === null) {
                    foreach ($this->splitStones($stoneRaw) as $token) {
                        [$candidate, $tokenAlias] = $this->matchStone($stones, $token);
                        if ($candidate !== null) {
                            $stone = $candidate;
                            $viaAlias = $tokenAlias;
                            $warnings[] = "wiele kamieni ({$stoneRaw}) → przyjęto {$stone->slug}";
                            break;
                        }
                    }
                }

                if ($stone === null) {
                    // kamień spoza słownika — rekord NIE wchodzi do bazy (kolejka ręczna)
                    $row->update(['state' => 'rejected', 'errors' => [
                        'blockers' => ["kamień spoza słownika: {$stoneRaw}"],
                    ]]);
                    $stats['rejected']++;
                    continue;
                }
                if ($viaAlias) {
                    $warnings[] = "kamień zmapowany aliasem: {$stoneRaw} → {$stone->slug}";
                }
            }

            $finish = $this->finishFromColor($specRaw['color'] ?? '');
            $imageUrl = collect($p['locales'] ?? [])->pluck('image_url')->filter()->first();

            $translations = [];
            foreach (['de', 'en', 'pl'] as $locale) {
                $catName = $categories[$categorySlug]->translation($locale)?->name ?? $categorySlug;
                $stoneName = $stone?->translation($locale)?->name;
                $translations[$locale] = $this->buildTranslation(
                    $locale, $modelNo, $catName, $stoneName, $finish,
                );
            }

            $normalized = [
                'model_no' => $modelNo,
                'category_slug' => $categorySlug,
                'stone_slug' => $stone?->slug,
                'cut_slug' => null,        // brak szlifu w starym ComUp
                'collection_slug' => null, // brak kolekcji w starym ComUp
                'finish' => $finish,
                'sizes' => null,           // rozmiarówka dojdzie z Comarcha (§5)
                'image_url' => $imageUrl,
                'spec' => array_filter([
                    'material' => '925 Silber',
                    'finish' => $finish,
                    'stone' => $stone?->slug,
                    'color_raw' => $specRaw['color'] ?? null,
                    'model_no' => $modelNo,
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

    /** Wartości spec z pierwszego locale, które je ma (PL ma najpełniejsze). */
    private function mergedSpec(array $locales): array
    {
        $out = [];
        foreach (['pl', 'de', 'en'] as $locale) {
            $table = $locales[$locale]['spec_table'] ?? [];
            foreach (self::SPEC_LABELS as $key => $labels) {
                if (isset($out[$key])) {
                    continue;
                }
                foreach ($labels as $label) {
                    foreach ($table as $tLabel => $value) {
                        if (mb_strtoupper(trim($tLabel, " .:")) === $label && filled($value)) {
                            $out[$key] = trim($value);
                            break 2;
                        }
                    }
                }
            }
        }

        return $out;
    }

    /** @return array{0: ?object, 1: bool} [kamień, czy dopasowano aliasem] */
    private function matchStone($stones, string $raw): array
    {
        $needle = mb_strtolower(trim($raw));

        $exact = $stones->first(
            fn ($s) => $s->translations->contains(
                fn ($t) => mb_strtolower($t->name) === $needle,
            ),
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

    /** „X, Y i Z" / „X und Y" / „X/Y" → tokeny do dopasowania pojedynczo. */
    private function splitStones(string $raw): array
    {
        $tokens = preg_split('/\s*(?:,|\/|\bi\b|\bund\b|\band\b)\s*/iu', $raw, -1, PREG_SPLIT_NO_EMPTY);

        return array_map('trim', $tokens ?: []);
    }

    /** KOLOR → wykończenie kanoniczne (silber|vergoldet|oxidiert|kombi). */
    private function finishFromColor(string $color): string
    {
        $c = mb_strtolower($color);
        $gilded = str_contains($c, 'pozłot') || str_contains($c, 'pozlot')
            || str_contains($c, 'gold') || str_contains($c, 'gild');
        $oxidised = str_contains($c, 'oksyd') || str_contains($c, 'oxid')
            || str_contains($c, 'oxyd');

        return match (true) {
            $gilded && $oxidised => 'kombi',
            $gilded => 'vergoldet',
            $oxidised => 'oxidiert',
            default => 'silber',
        };
    }

    /**
     * Deterministyczne treści AEO z pól rzeczywistych — szablony utrzymują
     * 40–60 słów z kamieniem i bez kamienia.
     */
    private function buildTranslation(string $locale, string $modelNo, string $catName, ?string $stoneName, string $finish): array
    {
        $finishLabel = self::FINISH_LABELS[$locale][$finish];
        $name = "{$catName} {$modelNo}";

        [$summary, $faq, $metaTitle, $alt] = match ($locale) {
            'de' => [
                "{$catName} {$modelNo} aus 925 Silber, Ausführung {$finishLabel}."
                .($stoneName ? " Der Stein ist {$stoneName}." : ' Das Stück kommt ohne Stein aus.')
                .' Die Oberflächenstruktur entsteht in der eigenen Goldschmiedewerkstatt und wiederholt sich in keinem zweiten Stück.'
                ." Erhältlich über unabhängige Boutiquen in Europa — die Verfügbarkeit erfragen Sie in Ihrer Verkaufsstelle unter der Modellnummer {$modelNo}. Jedes Stück wird einzeln gefertigt und geprüft.",
                array_values(array_filter([
                    ['q' => 'Wie frage ich nach diesem Modell?', 'a' => "Nennen Sie in der Boutique die Modellnummer {$modelNo}. Wir verkaufen nicht direkt an Endkundinnen — die Verfügbarkeit prüft Ihre Verkaufsstelle."],
                    ['q' => 'Wo kann ich das Stück kaufen?', 'a' => 'EvaStone verkauft über unabhängige Boutiquen und Juweliere in Europa. Die Verkaufsstellen finden Sie auf der Händlerkarte.'],
                    $stoneName ? ['q' => 'Welcher Stein ist verarbeitet?', 'a' => "Der Stein ist {$stoneName}. Wir arbeiten mit einer geschlossenen Liste von Edelsteinen; jeder Stein wird mit Namen angegeben."] : null,
                ])),
                "{$catName} {$modelNo} — 925 Silber | EvaStone",
                "{$catName} {$modelNo} aus 925 Silber".($stoneName ? " mit {$stoneName}" : '').', Oberflächenstruktur EvaStone',
            ],
            'en' => [
                "{$catName} {$modelNo} in 925 sterling silver, {$finishLabel} finish."
                .($stoneName ? " The stone is {$stoneName}." : ' This piece is designed without a stone.')
                .' The surface structure is created in our own goldsmith workshop and never repeats in any second piece.'
                ." Available through independent boutiques across Europe — please ask your local store for availability, quoting model number {$modelNo}. Every piece is made and inspected individually.",
                array_values(array_filter([
                    ['q' => 'How do I ask for this model?', 'a' => "Quote model number {$modelNo} at the boutique. We do not sell directly to end customers — your store checks availability."],
                    ['q' => 'Where can I buy this piece?', 'a' => 'EvaStone sells through independent boutiques and jewellers across Europe. You will find stockists on the retailer map.'],
                    $stoneName ? ['q' => 'Which stone is set in this piece?', 'a' => "The stone is {$stoneName}. We work with a closed list of gemstones; every stone is named explicitly."] : null,
                ])),
                "{$catName} {$modelNo} — 925 sterling silver | EvaStone",
                "{$catName} {$modelNo} in 925 sterling silver".($stoneName ? " with {$stoneName}" : '').', EvaStone surface structure',
            ],
            default => [
                "{$catName} {$modelNo} ze srebra próby 925, wykończenie: {$finishLabel}."
                .($stoneName ? " Kamień to {$stoneName}." : ' Egzemplarz zaprojektowany bez kamienia.')
                .' Struktura powierzchni powstaje w naszej pracowni złotniczej i nie powtarza się w żadnym drugim egzemplarzu.'
                ." Biżuteria dostępna jest w niezależnych butikach w Europie — o dostępność zapytaj w punkcie sprzedaży, podając numer modelu {$modelNo}. Każdy egzemplarz wykonujemy i sprawdzamy osobno.",
                array_values(array_filter([
                    ['q' => 'Jak zapytać o ten model?', 'a' => "Podaj w butiku numer modelu {$modelNo}. Nie prowadzimy sprzedaży bezpośredniej — dostępność sprawdza punkt sprzedaży."],
                    ['q' => 'Gdzie kupię ten egzemplarz?', 'a' => 'EvaStone sprzedaje przez niezależne butiki i salony jubilerskie w Europie. Punkty sprzedaży znajdziesz na mapie dystrybutorów.'],
                    $stoneName ? ['q' => 'Jaki kamień jest w tym egzemplarzu?', 'a' => "Kamień to {$stoneName}. Pracujemy na zamkniętej liście kamieni; każdy kamień podajemy z nazwy."] : null,
                ])),
                "{$catName} {$modelNo} — srebro próby 925 | EvaStone",
                "{$catName} {$modelNo} ze srebra próby 925".($stoneName ? " z kamieniem {$stoneName}" : '').', struktura powierzchni EvaStone',
            ],
        };

        return [
            'name' => $name,
            'slug_localized' => mb_strtolower($modelNo),
            'answer_summary' => $summary,
            'description' => null,
            'faq' => $faq,
            'meta_title' => $metaTitle,
            'meta_description' => mb_substr($summary, 0, 160),
            'image_alt' => $alt,
        ];
    }
}
