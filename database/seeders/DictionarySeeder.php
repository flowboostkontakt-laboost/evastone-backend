<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * §3.1 — słowniki są zamkniętymi listami seedowanymi, NIE edytowalnymi w CMS.
 * Rekord importu spoza tych list nie trafia do bazy (kolejka ręczna).
 *
 * Nazwy per locale pochodzą 1:1 ze starej strony (ComUp).
 */
class DictionarySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ---- kategorie: 6 pozycji (dok. 17 §2) --------------------------------
        $categories = [
            'pierscionki' => ['de' => ['Ringe', 'ringe'],        'en' => ['Rings', 'rings'],           'pl' => ['Pierścionki', 'pierscionki']],
            'bransolety'  => ['de' => ['Armreifen', 'armreifen'], 'en' => ['Bracelets', 'bracelets'],   'pl' => ['Bransolety', 'bransolety']],
            'kolczyki'    => ['de' => ['Ohrringe', 'ohrringe'],   'en' => ['Earrings', 'earrings'],     'pl' => ['Kolczyki', 'kolczyki']],
            'kolie'       => ['de' => ['Colliers', 'colliers'],   'en' => ['Necklaces', 'necklaces'],   'pl' => ['Kolie', 'kolie']],
            'zawieszki'   => ['de' => ['Anhänger', 'anhaenger'],  'en' => ['Pendants', 'pendants'],     'pl' => ['Zawieszki', 'zawieszki']],
            'sety'        => ['de' => ['Sets', 'sets'],           'en' => ['Sets', 'sets'],             'pl' => ['Sety', 'sety']],
        ];

        $position = 0;
        foreach ($categories as $slug => $locales) {
            DB::table('categories')->updateOrInsert(['slug' => $slug], ['position' => ++$position, 'created_at' => $now, 'updated_at' => $now]);
            $categoryId = DB::table('categories')->where('slug', $slug)->value('id');
            foreach ($locales as $locale => [$name, $slugLocalized]) {
                DB::table('category_translations')->updateOrInsert(
                    ['category_id' => $categoryId, 'locale' => $locale],
                    ['name' => $name, 'slug_localized' => $slugLocalized],
                );
            }
        }

        // ---- kamienie: 8 pozycji (dok. 03) — lista zamknięta ------------------
        $stones = [
            'topaz'               => ['de' => ['Topas', 'topas'],                                'en' => ['topaz', 'topaz'],                        'pl' => ['topaz', 'topaz']],
            'tanzanit'            => ['de' => ['Tansanit', 'tansanit'],                          'en' => ['tanzanite', 'tanzanite'],                'pl' => ['tanzanit', 'tanzanit']],
            'szmaragd'            => ['de' => ['Smaragd', 'smaragd'],                            'en' => ['emerald', 'emerald'],                    'pl' => ['szmaragd', 'szmaragd']],
            'turmalin-rozowy'     => ['de' => ['rosa Turmalin', 'rosa-turmalin'],                'en' => ['pink tourmaline', 'pink-tourmaline'],    'pl' => ['różowy turmalin', 'rozowy-turmalin']],
            'szafir-pomaranczowy' => ['de' => ['oranger Saphir', 'oranger-saphir'],              'en' => ['orange sapphire', 'orange-sapphire'],    'pl' => ['pomarańczowy szafir', 'pomaranczowy-szafir']],
            'szafir-bialy'        => ['de' => ['weißer Saphir', 'weisser-saphir'],               'en' => ['white sapphire', 'white-sapphire'],      'pl' => ['biały szafir', 'bialy-szafir']],
            'diament-surowy'      => ['de' => ['Rohdiamantwürfel', 'rohdiamantwuerfel'],         'en' => ['rough diamond cube', 'rough-diamond-cube'], 'pl' => ['kostka surowego diamentu', 'kostka-surowego-diamentu']],
            'diament-czarny'      => ['de' => ['schwarzer Diamantwürfel', 'schwarzer-diamantwuerfel'], 'en' => ['black diamond cube', 'black-diamond-cube'], 'pl' => ['kostka czarnego diamentu', 'kostka-czarnego-diamentu']],
        ];

        $position = 0;
        foreach ($stones as $slug => $locales) {
            DB::table('stones')->updateOrInsert(['slug' => $slug], ['position' => ++$position, 'created_at' => $now, 'updated_at' => $now]);
            $stoneId = DB::table('stones')->where('slug', $slug)->value('id');
            foreach ($locales as $locale => [$name, $slugLocalized]) {
                DB::table('stone_translations')->updateOrInsert(
                    ['stone_id' => $stoneId, 'locale' => $locale],
                    ['name' => $name, 'slug_localized' => $slugLocalized],
                );
            }
        }

        // ---- szlify: 4 pozycje ------------------------------------------------
        $cuts = [
            'rund'        => ['de' => ['rund', 'rund'],               'en' => ['round', 'round'],             'pl' => ['okrągły', 'okragly']],
            'quadratisch' => ['de' => ['quadratisch', 'quadratisch'], 'en' => ['square', 'square'],           'pl' => ['kwadratowy', 'kwadratowy']],
            'rechteckig'  => ['de' => ['rechteckig', 'rechteckig'],   'en' => ['rectangular', 'rectangular'], 'pl' => ['prostokątny', 'prostokatny']],
            'navette'     => ['de' => ['Navette', 'navette'],         'en' => ['navette', 'navette'],         'pl' => ['markiza', 'markiza']],
        ];

        $position = 0;
        foreach ($cuts as $slug => $locales) {
            DB::table('cuts')->updateOrInsert(['slug' => $slug], ['position' => ++$position, 'created_at' => $now, 'updated_at' => $now]);
            $cutId = DB::table('cuts')->where('slug', $slug)->value('id');
            foreach ($locales as $locale => [$name, $slugLocalized]) {
                DB::table('cut_translations')->updateOrInsert(
                    ['cut_id' => $cutId, 'locale' => $locale],
                    ['name' => $name, 'slug_localized' => $slugLocalized],
                );
            }
        }

        // ---- kolekcje: 3 pozycje (z danych ComUp) -----------------------------
        $collections = [
            'struktur' => ['de' => 'Struktur', 'en' => 'Structure', 'pl' => 'Struktura'],
            'raster'   => ['de' => 'Raster',   'en' => 'Grid',      'pl' => 'Raster'],
            'stein'    => ['de' => 'Stein',    'en' => 'Stone',     'pl' => 'Kamień'],
        ];

        $position = 0;
        foreach ($collections as $slug => $locales) {
            DB::table('collections')->updateOrInsert(['slug' => $slug], ['position' => ++$position, 'created_at' => $now, 'updated_at' => $now]);
            $collectionId = DB::table('collections')->where('slug', $slug)->value('id');
            foreach ($locales as $locale => $name) {
                DB::table('collection_translations')->updateOrInsert(
                    ['collection_id' => $collectionId, 'locale' => $locale],
                    ['name' => $name],
                );
            }
        }
    }
}
