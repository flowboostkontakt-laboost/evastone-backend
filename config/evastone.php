<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | i18n — §8
    |--------------------------------------------------------------------------
    */
    'locales' => ['de', 'en', 'pl'],
    'x_default' => 'de',

    // localized pierwszy segment katalogu: /{locale}/{section}/{category}/{slug}
    'catalog_sections' => [
        'de' => 'schmuck',
        'en' => 'jewellery',
        'pl' => 'bizuteria',
    ],

    /*
    |--------------------------------------------------------------------------
    | Import z ComUp — §4
    |--------------------------------------------------------------------------
    | Źródło: statyczna kopia starej strony (repo ComUp zrzucone do plików).
    | W kontenerze montowana pod /mirror (docker-compose.yml).
    */
    'import' => [
        'mirror_path' => env('COMUP_MIRROR_PATH', '/mirror'),

        // §4.4 — cena w treści → blok. Regex z dok. 17 §2 pkt 4.
        'price_regex' => '/\d+[\s,.]?\d*\s*(zł|PLN|€|EUR)/iu',

        // czarna lista słów (dok. 17) — do uzupełnienia przez klientkę
        'blacklist' => [],
    ],

    // §3.3 — zamknięta lista wykończeń występujących w danych źródłowych
    // (data-wykonczenie w ComUp; koreluje z sufiksem model_no: AG/AK/OX/AU)
    'finishes' => ['silber', 'kombi', 'oxidiert', 'vergoldet'],

    /*
    |--------------------------------------------------------------------------
    | Aliasy kamieni — warianty zapisu z ComUp → slug słownika (lista 8)
    |--------------------------------------------------------------------------
    | WYŁĄCZNIE inne zapisy TEGO SAMEGO kamienia (język, liczba mnoga,
    | literówki, odmiana handlowa topazu). To nie jest furtka do
    | rozszerzania zamkniętej listy — nowy kamień wymaga decyzji klienta
    | i zmiany w DictionarySeeder (PR-review, patrz dok. 21 §3.1).
    | Dopasowanie case-insensitive po mb_strtolower.
    */
    'stone_aliases' => [
        'diament-czarny' => [
            'czarny diament', 'czarne diamenty', 'kostka czarnego diamentu',
            'schwarz diamant', 'schwarz diamanten', 'schwarzer diamant',
            'schwarzer diamanten', 'schwarze diamanten', 'black diamond',
            'black diamonds', 'czarny diamnet', 'czarny diamend',
        ],
        'diament-surowy' => [
            'würfel rohdiamant', 'würfel rohdiamanten', 'rohdiamant',
            'rohdiamanten', 'surowy diament', 'kostka surowego diamentu',
            'kostki surowego diamentu', 'rough diamond', 'rough diamonds',
            'raw diamond', 'raw diamond cube',
        ],
        'szafir-bialy' => [
            'biały szafir', 'białe szafiry', 'weißer saphir', 'weiße saphire',
            'weisse saphire', 'white sapphire', 'white sapphires',
        ],
        'szafir-pomaranczowy' => [
            'pomarańczowy szafir', 'pomarańczowe szafiry', 'oranger saphir',
            'orange saphire', 'orange sapphire',
        ],
        // odmiany handlowe topazu — mapowane na topaz z ostrzeżeniem w raporcie
        'topaz' => [
            'topas', 'swiss topaz', 'swiss topas', 'sky topaz', 'niebieski topaz',
            'blautopas', 'blue topaz', 'blautopaz', 'blau topaz', 'blau topas',
        ],
        'tanzanit' => ['tansanit', 'tanzanite'],
        'szmaragd' => ['smaragd', 'smaragde', 'emerald', 'emeralds', 'szmaragdy'],
        'turmalin-rozowy' => [
            'różowy turmalin', 'rosa turmalin', 'pink tourmaline',
            'rosa turmaline', 'różowe turmaliny',
        ],

        // rozszerzenie listy (decyzja właściciela 30.08.2026) + literówki z ComUp
        'diament-naturalny' => [
            'naturalny diament', 'naturalne diamenty', 'natyralny diament',
            'natyralni diamenty', 'natyralne diamenty', 'naturalne dimenty',
            'naturalne diamnety', 'naturalne diamnety', 'naturalny szary diament',
            'szary naturalny diament', 'szare naturalne diamenty',
            '5 naturalnych diamentów', 'brilliant', 'brylant',
            'natural diamond', 'natural diamonds', 'natürlicher diamant',
            'natürliche diamanten', 'naturalny i czarny diamenty',
        ],
        'rubin' => ['rubin', 'rubiny', 'ruby', 'rubine', 'rubies'],
        'perla' => ['perła', 'perla', 'perły', 'perle', 'perlen', 'pearl', 'pearls'],
        'spektrolit' => ['spektrolit', 'spectrolite', 'spektrolith'],
        'ametyst' => ['ametyst', 'ametysty', 'amethyst'],
        'granat' => ['granat', 'granaty', 'garnat', 'garnet', 'granate'],
        'opal-dublet' => [
            'dublet opal', 'dublet opalu', 'dublette opal', 'opal dublette',
            'opal-dublette', 'opal dublet', 'opal doublet', 'doublet opal',
        ],
        'opal' => ['opal'],
        'szafir-niebieski' => [
            'niebieski szafir', 'niebieskie szafiry', 'niebieski szafiry',
            'blauer saphir', 'blaue saphire', 'blau saphir',
            'blue sapphire', 'blue sapphires',
        ],
        'peridot' => ['peridot', 'perydot', 'oliwin'],
        'oniks' => ['oniks', 'onyx'],
        'spinel' => ['spinel', 'spinell'],
        'hematyt' => ['hematyt', 'hämatit', 'hematite'],
        'labradoryt' => ['labradoryt', 'labrodoryt', 'labradorit', 'labradorite'],
        'obsydian-sniezny' => [
            'obsydian śnieżny', 'śnieżny obsydian', 'schneeobsidian',
            'snowflake obsidian',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | RODO — §15
    |--------------------------------------------------------------------------
    */
    'ip_pepper' => env('IP_PEPPER', ''),
    'leads_retention_months' => 24,
];
