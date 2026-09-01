<?php

declare(strict_types=1);

namespace App\Domain\Import\Actions;

use App\Domain\Import\Models\ImportProductRaw;
use Illuminate\Support\Facades\DB;

/**
 * §4 krok 3, źródło „comup-dump" — PEŁNY katalog ze starej platformy B2B
 * ComUp (2928 modeli). W przeciwieństwie do źródła „live" (publiczny crawl
 * ~550 pozycji) mamy tu prawdziwe opisy, nazwy serii, wykończenia, wymiary
 * i pełną listę kamieni — dane wprost z produkcyjnej bazy MySQL (zrzut
 * załadowany lokalnie do schematu `comup_legacy`, połączenie o tej samej
 * nazwie w config/database.php).
 *
 * Payload = surowy rekord per produkt (unia 3 wersji językowych), BEZ
 * czyszczenia. Zdjęcia (binaria) NIE są tu pobierane — leżą za loginem
 * dystrybutora na serwerze ComUp; payload przenosi tylko referencje
 * (product_id + nazwy plików) do osobnej fazy pobrania przez FTP.
 *
 * model_no ze starego katalogu bywa niepowtarzalny tylko „prawie" (kilka
 * rekordów dzieli ten sam numer, 2 są puste), a products.model_no i
 * (locale, slug_localized) są UNIQUE — dedup jest tu obowiązkowy, inaczej
 * publish-upsert zjadłby część produktów.
 */
class PullFromComupDump
{
    private const CONN = 'comup_legacy';

    /** lang_id w ComUp → locale aplikacji */
    private const LANG = [1 => 'pl', 2 => 'en', 8 => 'de'];

    public function handle(string $batchId, ?int $limit = null): array
    {
        $stats = ['found' => 0, 'inserted' => 0, 'unchanged' => 0, 'skipped_deleted' => 0];

        $db = DB::connection(self::CONN);

        // --- słowniki referencyjne załadowane raz, mapy w pamięci ------------
        $catNames = $this->translationMap($db, 'product_category_translations', 'product_category_id');
        $shapeNames = $this->translationMap($db, 'product_shape_translations', 'product_shape_id');
        $seriesNames = $this->translationMap($db, 'product_series_translations', 'product_series_id');

        // kamień/materiał per produkt (nazwa pl słownika, przez tabele pivot)
        $stonesByProduct = $this->pivotNames(
            $db, 'product_filter_stones', 'product_stone_id',
            'product_stones', 'product_stone_translations',
        );
        $materialsByProduct = $this->pivotNames(
            $db, 'product_filter_materials', 'product_material_id',
            'product_materials', 'product_material_translations',
        );

        // tłumaczenia produktów: product_id → locale → pola
        $trById = [];
        $db->table('product_translations')->orderBy('id')
            ->get(['product_id', 'lang_id', 'name', 'subname', 'text', 'slug', 'profile_img_alt'])
            ->each(function ($t) use (&$trById): void {
                $locale = self::LANG[$t->lang_id] ?? null;
                if ($locale === null) {
                    return;
                }
                $trById[$t->product_id][$locale] = [
                    'name' => trim((string) $t->name),
                    'subname' => trim((string) $t->subname),
                    'text' => (string) $t->text,
                    'slug' => trim((string) $t->slug),
                    'alt' => trim((string) $t->profile_img_alt),
                ];
            });

        // zdjęcia galerii: product_id → [{photo_id, file}]
        $imagesByProduct = [];
        $db->table('product_images')->where('active', 1)->whereNull('deleted_at')
            ->orderBy('order')->get(['id', 'product_id', 'file'])
            ->each(function ($img) use (&$imagesByProduct): void {
                $imagesByProduct[$img->product_id][] = ['photo_id' => $img->id, 'file' => $img->file];
            });

        // --- produkty --------------------------------------------------------
        $query = $db->table('products')->whereNull('deleted_at')->orderBy('id');
        if ($limit !== null) {
            $query->limit($limit);
        }

        $seenModelNo = [];   // dedup case-insensitive w obrębie przebiegu

        foreach ($query->cursor() as $p) {
            $stats['found']++;

            $locales = [];
            foreach (self::LANG as $locale) {
                $locales[$locale] = $trById[$p->id][$locale] ?? null;
            }

            // model_no: pierwsza niepusta nazwa (pl→en→de); pusta → syntetyczny
            $modelNo = collect(['pl', 'en', 'de'])
                ->map(fn ($l) => $locales[$l]['name'] ?? '')
                ->first(fn ($n) => $n !== '') ?: '';
            $modelNo = trim($modelNo);
            if ($modelNo === '') {
                $modelNo = 'COMUP-'.$p->id;
            }

            // dedup: ten sam numer katalogowy na kilku rekordach → sufiks id
            $key = mb_strtolower($modelNo);
            if (isset($seenModelNo[$key])) {
                $modelNo .= '-'.$p->id;
            }
            $seenModelNo[$key] = true;

            $payload = [
                'comup_id' => (int) $p->id,
                'model_no' => $modelNo,
                'active' => (int) $p->active === 1,
                'category' => [
                    'id' => (int) $p->category_id,
                    'names' => $catNames[$p->category_id] ?? [],
                ],
                'finish_shape' => $shapeNames[$p->product_shape_id] ?? [],
                'series' => $p->product_series_id > 0 ? ($seriesNames[$p->product_series_id] ?? []) : [],
                'materials' => $materialsByProduct[$p->id] ?? [],
                'stones_raw' => $stonesByProduct[$p->id] ?? [],
                'width' => trim((string) $p->width) ?: null,
                'weight' => (float) $p->weight ?: null,
                'image' => [
                    'profile_img' => trim((string) $p->profile_img) ?: null,
                    'gallery' => $imagesByProduct[$p->id] ?? [],
                ],
                'locales' => $locales,
            ];

            $rowHash = hash('sha256', json_encode($this->ksortDeep($payload), JSON_UNESCAPED_UNICODE));

            if (ImportProductRaw::where('source', 'comup-dump')->where('row_hash', $rowHash)->exists()) {
                $stats['unchanged']++;
                continue;
            }

            ImportProductRaw::create([
                'batch_id' => $batchId,
                'source' => 'comup-dump',
                'external_id' => (string) $p->id,
                'payload' => $payload,
                'row_hash' => $rowHash,
                'state' => 'raw',
            ]);
            $stats['inserted']++;
        }

        return $stats;
    }

    /** id słownika → [locale => nazwa]. */
    private function translationMap($db, string $table, string $fk): array
    {
        $out = [];
        $db->table($table)->get([$fk, 'lang_id', 'name'])->each(function ($r) use (&$out, $fk): void {
            $locale = self::LANG[$r->lang_id] ?? null;
            if ($locale !== null) {
                $out[$r->{$fk}][$locale] = trim((string) $r->name);
            }
        });

        return $out;
    }

    /**
     * Tabela pivot (product_id ↔ id słownika) → product_id => [nazwy pl].
     * Nazwa pl wystarcza — mapowanie na słownik aplikacji jest po aliasach.
     */
    private function pivotNames($db, string $pivot, string $fk, string $dict, string $dictTrans): array
    {
        $names = [];
        $db->table($dictTrans)->where('lang_id', 1)->get([$dict === 'product_stones' ? 'product_stone_id' : 'product_material_id', 'name'])
            ->each(function ($r) use (&$names, $dict): void {
                $id = $dict === 'product_stones' ? $r->product_stone_id : $r->product_material_id;
                $names[$id] = trim((string) $r->name);
            });

        $out = [];
        $db->table($pivot)->whereNull('deleted_at')->orderBy('id')->get(['product_id', $fk])
            ->each(function ($r) use (&$out, $fk, $names): void {
                $name = $names[$r->{$fk}] ?? null;
                if (filled($name)) {
                    $out[$r->product_id][] = $name;
                }
            });

        return $out;
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
