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
            'schwarz diamant', 'schwarzer diamant', 'schwarzer diamanten',
            'schwarze diamanten', 'black diamond', 'black diamonds',
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
            'topas', 'swiss topaz', 'sky topaz', 'niebieski topaz',
            'blautopas', 'blue topaz', 'blautopaz',
        ],
        'tanzanit' => ['tansanit', 'tanzanite'],
        'szmaragd' => ['smaragd', 'emerald'],
        'turmalin-rozowy' => [
            'różowy turmalin', 'rosa turmalin', 'pink tourmaline',
            'rosa turmaline', 'różowe turmaliny',
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
