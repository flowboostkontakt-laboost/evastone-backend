#!/usr/bin/env bash
# Render — start web serwisu (dok. deploy). Idempotentne: bezpieczne przy każdym redeployu.
set -euo pipefail

php artisan storage:link || true
php artisan filament:assets

# cache pod produkcję (env muszą być ustawione w Render przed startem)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# schemat + słowniki zamknięte (seeder jest idempotentny — updateOrInsert)
php artisan migrate --force
php artisan db:seed --class=DictionarySeeder --force

# serwer HTTP na porcie z Rendera
exec php artisan serve --host 0.0.0.0 --port "${PORT:-8000}"
