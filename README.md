# EvaStone — back-end (Laravel)

Implementacja wg `21_Dokumentacja_techniczna_backend_Laravel.md` (kontrakt v1.0, 30.08.2026).

## Stack

PHP 8.4 · Laravel 12 · MySQL 8.0 · Redis · Filament 3 · spatie/laravel-medialibrary · Pest.
Środowisko lokalne: Docker (`docker-compose.yml` — kontenery app/queue/mysql/redis, bez instalowania PHP na hoście).

## Uruchomienie

```bash
docker compose up -d
docker compose exec app php artisan migrate --seed
```

Aplikacja: http://localhost:8080 · Panel CMS (Filament): http://localhost:8080/admin
(login `admin@evastone.eu`, hasło `password` — lokalnie; na staging/prod przez `ADMIN_PASSWORD`).

Kopia starej strony (ComUp) montowana jest w kontenerze pod `/mirror`
(katalog `../eva` — patrz `docker-compose.yml`).

## Migracja z ComUp (§4)

Kolejność obowiązkowa:

```bash
php artisan db:seed                      # 1. słowniki (zamknięte listy)
php artisan comarch:sync                 # 2. słownik Comarch (skip bez COMARCH_BASE_URL)
php artisan comup:pull                   # 3. dump → import_products_raw  (zwraca batch ULID)
php artisan import:normalize {batch}     # 4. mapowanie na słowniki
php artisan import:validate {batch}      # 5. reguły blokujące (§4.4)
php artisan import:report {batch}        # 6. CSV rozbieżności dla klientki (od razu!)
php artisan import:publish --category=pierscionki --dry-run
php artisan import:publish               # 7. fale publikacji (upsert po model_no)
```

Akceptacja treści (ścieżka krytyczna §7.3): w Filament (bulk action „Zatwierdź
komplet tłumaczeń") albo hurtowo z CLI:

```bash
php artisan catalog:approve --category=pierscionki --by="klientka"
php artisan import:publish               # przeliczy statusy → published
```

Idempotencja: `row_hash` (staging, unikat source+hash) i `source_hash`
(products) — wielokrotny przebieg nie duplikuje niczego.

## Testy — gate'y CI (§16)

```bash
docker compose exec app php artisan test
```

- `test_product_schema_never_contains_offers` — schema Product bez offers/price
- `test_products_table_has_no_price_column` — zakaz pola price w modelu
- `test_no_price_in_product_description` — regex cenowy
- pipeline: pełna migracja, idempotencja, publikacja wymaga 3× approved

## Stan realizacji względem §18

| # | Blok | Stan |
|---|------|------|
| 1 | Setup, i18n, słowniki | ✅ |
| 2 | Model katalogu + Filament + bulk approve | ✅ (ProductResource, ImportReview, Lead, widget KPI) |
| 3 | Sync Comarch | 🟡 szkielet (Action+komenda+cron+cache; mapowanie ERP czeka na dostęp do API) |
| 4 | Pipeline importu ComUp + raport | ✅ |
| 5 | Media + wideo | 🟡 galeria+alt-y per locale ✅; konwersje w kolejce zdefiniowane; wideo — brak plików w źródle (404 w ComUp) |
| 6 | Karta produktu + schema + hreflang | 🟡 route+widok+schema+hreflang(approved only) ✅; sitemapy — TODO |
| 7 | Home + LP1/LP2 + leady | 🟡 modele+tabele+LeadResource ✅; landingi i formularze — TODO |
| 8 | Certyfikaty /c/{uid} | 🟡 model+tabela+ULID ✅; route+widok — TODO |
| 9 | Tenancy + demo | 🟡 tabele tenants/demo_prices ✅; stancl/tenancy + bezpieczniki — TODO |
| 10 | Warstwa zszywająca ComUp | ⬜ |
| 11 | llms.txt, robots, GA4 | ⬜ |

Uwaga do §3.3: enum wykończeń w danych rzeczywistych to
`silber|kombi|oxidiert|vergoldet` (z `data-wykonczenie` ComUp; koreluje z
sufiksem model_no AG/AK/OX/AU) — wartości przykładowe ze spec
(`oxidiert|poliert|strukturiert`) nie występują w źródle.

## Architektura (§2)

Logika w Actions (`app/Domain/*/Actions`), kontrolery = walidacja+wywołanie+widok,
Filament woła te same Actions. Jedna klasa = jedna operacja.
