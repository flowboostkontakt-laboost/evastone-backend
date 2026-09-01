# Deploy na Oracle Cloud Always Free (VM)

Darmowa na zawsze maszyna z prawdziwym dyskiem — cały stack (web + worker +
MySQL + Redis + media) chodzi jednym `docker compose`. Bez S3/R2: media leżą
na wolumenie VM. Bez usypiania, bez wygasania bazy.

## 1. Utwórz VM (konsola Oracle Cloud)

1. Zarejestruj się na oracle.com/cloud/free (karta do weryfikacji — **nie obciąża**,
   wybierz konto **Always Free**).
2. **Compute → Instances → Create instance**:
   - Image: **Ubuntu 24.04** (lub 22.04)
   - Shape: **Ampere (ARM) VM.Standard.A1.Flex** — Always Free do 4 OCPU / 24 GB RAM.
     Daj np. 2 OCPU / 12 GB (zapas dla MySQL + konwersji webp).
   - Dodaj klucz SSH (pobierz prywatny).
3. **Networking → Security List → Ingress Rules** — dodaj reguły:
   - `0.0.0.0/0` TCP **80`**
   - `0.0.0.0/0` TCP **443**
   (port 22 jest już otwarty).

## 2. Postaw aplikację

SSH na VM (`ssh ubuntu@<PUBLIC_IP>`), potem:

```bash
# repo jest PRYWATNE — zaloguj git do GitHuba raz:
sudo apt-get update && sudo apt-get install -y gh
gh auth login          # wybierz GitHub.com → HTTPS → zaloguj przez przeglądarkę/kod
gh repo clone flowboostkontakt-laboost/evastone-backend
cd evastone-backend
bash deploy/oracle-bootstrap.sh          # instaluje Docker, otwiera 80/443
cp .env.production.example .env.production
nano .env.production                     # uzupełnij (niżej)
newgrp docker
docker compose -f docker-compose.prod.yml up -d --build
```

### Co uzupełnić w `.env.production`
- `DOMAIN` i `APP_URL` — najprościej `<PUBLIC_IP>.sslip.io` (darmowy TLS bez kupowania
  domeny; np. `140.238.1.2.sslip.io`). Docelowo prawdziwa domena, np. `evastone.eu`.
- `DB_PASSWORD`, `DB_ROOT_PASSWORD` — mocne hasła.
- `COMUP_LEGACY_PASSWORD` — hasło do bazy ComUp (do jednorazowego importu).
- `IP_PEPPER` — losowy ciąg (RODO).

APP_KEY jest już wpisany w przykładzie. Caddy sam załatwi HTTPS na `DOMAIN`.

## 3. Załaduj katalog (2755 produktów ze zdjęciami)

Migracje i słowniki wchodzą same przy starcie web. Katalog odtwarzasz pipeline'em
(czyta zdalną bazę ComUp, zdjęcia z `evastone.comup.pl`):

```bash
docker compose -f docker-compose.prod.yml exec app bash -lc \
  'B=$(php artisan comup:pull --source=dump | grep Batch | awk "{print \$2}"); \
   php artisan import:normalize $B && php artisan import:validate $B && php artisan import:publish'
```

Gotowe: strona pod `https://<DOMAIN>/de/`, panel pod `https://<DOMAIN>/admin`.

## Uwagi
- Serwer HTTP: `php artisan serve` za Caddy — wystarcza; docelowo FrankenPHP/nginx.
- Backup: `mysqldump` z wolumenu `dbdata` albo snapshot bloku Oracle.
- ARM: obrazy (php84, mysql, redis, caddy) są multi-arch — działają na Ampere.
