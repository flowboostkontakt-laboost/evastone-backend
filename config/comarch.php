<?php

declare(strict_types=1);

// §5 — sync słownika Comarch: read-only, źródło nadrzędne dla model_no,
// indeksów i rozmiarówki. Przy braku poświadczeń komenda loguje i kończy się
// czysto — katalog działa na ostatnim znanym stanie.
return [
    'base_url' => env('COMARCH_BASE_URL'),
    'token' => env('COMARCH_TOKEN'),
    'timeout' => 30,
    'retries' => 3,
    'cache_ttl_hours' => 6,
];
