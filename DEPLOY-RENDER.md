# Deploy na Render

Backend to Laravel + Postgres + Redis + Filament w Dockerze. Vercel go nie
uruchomi (statyka/serverless); Render bierze naszego Dockera i daje managed
Postgresa oraz Key Value (Redis).

## Architektura na Render

| Element | Serwis Render | Uwaga |
|---|---|---|
| Aplikacja + panel `/admin` | Web (Docker, `Dockerfile.render`) | serwuje stronę i Filament |
| Kolejka (import, webp, mail) | Worker (ten sam obraz) | `queue:work` |
| Baza aplikacji | Postgres (managed) | migracje są PG-kompatybilne |
| Cache/kolejka | Key Value (Redis) | przez `predis`, bez rozszerzeń |
| Zdjęcia/media | Cloudflare R2 / S3 | Render jest bezstanowy — dysk nie przetrwa redeployu |
| Baza ComUp (import) | — (zdalna `kacper.comup.pl`) | czytana raz przez pipeline, read-only |

## Kroki

1. **Repo na GitHub** — Render deployuje z gita (kod jest już w repo lokalnym).
2. **Blueprint** — panel Render → New → Blueprint → wskaż repo. `render.yaml`
   tworzy web + worker + Postgres + Redis.
3. **Sekrety** (panel, `sync:false`):
   - `APP_KEY` → `php artisan key:generate --show`
   - `APP_URL` → URL serwisu web (po 1. deployu)
   - `COMUP_LEGACY_DATABASE/USERNAME/PASSWORD` → dane bazy ComUp
   - `AWS_*` → bucket R2 na media
4. **Media (R2)** — utwórz darmowy bucket Cloudflare R2 (10 GB), token S3-API,
   wpisz `AWS_*`. Bez tego zdjęcia znikną przy redeployu.

## Załadowanie katalogu (2755 produktów) — po pierwszym deployu

Migracje i słowniki lecą automatycznie przy starcie. Katalog odtwarzasz
pipeline'em (Render → Shell na serwisie web), czytając zdalną bazę ComUp:

```bash
BATCH=$(php artisan comup:pull --source=dump | grep Batch | awk '{print $2}')
php artisan import:normalize $BATCH
php artisan import:validate $BATCH
php artisan import:publish
```

Zdjęcia pobiorą się z `evastone.comup.pl` na dysk media (R2). Konwersje webp
przetworzy worker. Wszystko idempotentne — można powtarzać.

## Uwagi

- Serwer HTTP to `php artisan serve` (wystarcza na start/preview). Docelowo
  produkcyjnie warto FrankenPHP lub nginx+fpm.
- `MAIL_MAILER=log` do czasu konfiguracji SMTP + SPF/DKIM/DMARC (CTO, dok. 07 §6).
- Plan `free` Postgresa wygasa po 90 dniach — podnieś przed produkcją.
