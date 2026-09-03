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

        // stary serwer ComUp serwuje pliki /upload/... publicznie (statyczne,
        // bez logowania) — źródło zdjęć dla migracji pełnego katalogu (dump).
        'comup_upload_base' => env('COMUP_UPLOAD_BASE', 'https://evastone.comup.pl'),

        // §4.4 — cena w treści → blok. Regex z dok. 17 §2 pkt 4.
        'price_regex' => '/\d+[\s,.]?\d*\s*(zł|PLN|€|EUR)/iu',

        // czarna lista słów (dok. 17) — do uzupełnienia przez klientkę
        'blacklist' => [],
    ],

    // webhook CRM dla leadów B2B (spec 16 §4.1). Puste → tylko log.
    'lead_webhook_url' => env('LEAD_WEBHOOK_URL', ''),

    // zlokalizowane slugi strony podziękowania po formularzu B2B (site.json → slugs)
    'danke_slugs' => ['de' => 'danke', 'en' => 'thank-you', 'pl' => 'dziekujemy'],

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
            'naturalny diament kostka', 'naturalne diamenty kostka',
            'kostka naturalnego diamentu', 'natürlicher diamantwürfel',
            'natural diamond cube',
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
        'oniks' => ['oniks', 'onyx', 'onyks'],
        // II rozszerzenie 31.08.2026 — warianty z pełnego katalogu B2B ComUp
        'turmalin' => ['turmalin', 'tourmaline', 'zielony turmalin', 'grüner turmalin', 'green tourmaline', 'zielone turmaliny'],
        'lapis-lazuli' => ['lapis lazuli', 'lapislazuli', 'lapis-lazuli'],
        'tygrysie-oko' => ['tygrysie oko', 'tigerauge', "tiger's eye", 'tigers eye'],
        'diament-bialy' => ['biały diament', 'białe diamenty', 'weißer diamant', 'white diamond'],
        'szafir-rozowy' => ['różowy szafir', 'różowe szafiry', 'rosa saphir', 'pink sapphire'],
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
    | Newsletter i e-mail marketing — dok. 07
    |--------------------------------------------------------------------------
    | B2C: WYŁĄCZNIE double opt-in. Dziennik zgód musi utrwalać brzmienie
    | zgody z chwili wyrażenia — dlatego wersjonujemy treść i zapisujemy
    | jej snapshot w consents.evidence. Nadawca imienny (nigdy noreply@),
    | reply-to obsługiwany przez człowieka (§5).
    */
    'newsletter' => [
        // podbij wersję przy każdej zmianie treści zgody (audyt RODO)
        'consent_version' => env('NEWSLETTER_CONSENT_VERSION', 'nl-2026-08-1'),

        // brzmienie zgody per locale (PL robocze — do akceptacji klientki, dok. 07 §4.1)
        'consent_text' => [
            'de' => 'Ich möchte den EvaStone-Newsletter (Pflege, Händlerkarte, Verfügbarkeit) erhalten und willige in die Verarbeitung meiner E-Mail-Adresse zu diesem Zweck ein. Die Einwilligung kann ich jederzeit über den Abmeldelink widerrufen.',
            'en' => 'I would like to receive the EvaStone newsletter (jewellery care, retailer map, availability) and consent to the processing of my email address for this purpose. I can withdraw this consent at any time via the unsubscribe link.',
            'pl' => 'Chcę otrzymywać newsletter EvaStone (pielęgnacja, mapa dystrybutorów, dostępność) i wyrażam zgodę na przetwarzanie mojego adresu e-mail w tym celu. Zgodę mogę wycofać w każdej chwili przez link rezygnacji.',
        ],

        // nadawca — imienny, ustawiany przez KONIK; NIGDY noreply@ (dok. 07 §5)
        'from_address' => env('NEWSLETTER_FROM_ADDRESS', 'newsletter@evastone.eu'),
        'from_name' => env('NEWSLETTER_FROM_NAME', 'EvaStone'),
        'reply_to' => env('NEWSLETTER_REPLY_TO', 'kontakt@evastone.eu'),
    ],

    /*
    |--------------------------------------------------------------------------
    | RODO — §15
    |--------------------------------------------------------------------------
    */
    'ip_pepper' => env('IP_PEPPER', ''),
    'leads_retention_months' => 24,
];
