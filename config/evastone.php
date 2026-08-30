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
    | RODO — §15
    |--------------------------------------------------------------------------
    */
    'ip_pepper' => env('IP_PEPPER', ''),
    'leads_retention_months' => 24,
];
