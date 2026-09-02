#!/usr/bin/env bash
# Render — start web serwisu (dok. deploy). Idempotentne: bezpieczne przy każdym redeployu.
set -euo pipefail

# APP_KEY: użyj z env; jeśli pusty (grupa sync:false nieuzupełniona), wygeneruj —
# żeby deploy nie padał na MissingAppKeyException. Dla trwałych sesji ustaw
# stały APP_KEY w zmiennych środowiskowych Render.
if [ -z "${APP_KEY:-}" ]; then
  export APP_KEY="$(php -r 'echo "base64:".base64_encode(random_bytes(32));')"
  echo ">> APP_KEY nie był ustawiony — wygenerowano tymczasowy na czas tego uruchomienia"
fi

php artisan storage:link || true
php artisan filament:assets

# cache pod produkcję (env muszą być ustawione w Render przed startem)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# schemat + słowniki zamknięte (seeder jest idempotentny — updateOrInsert)
php artisan migrate --force
# DatabaseSeeder = słowniki (DictionarySeeder) + konto admina (admin@evastone.eu,
# hasło z ADMIN_PASSWORD lub 'password'). Idempotentne (updateOrCreate/updateOrInsert).
php artisan db:seed --class=DatabaseSeeder --force

# serwer HTTP na porcie z Rendera
exec php artisan serve --host 0.0.0.0 --port "${PORT:-8000}"
