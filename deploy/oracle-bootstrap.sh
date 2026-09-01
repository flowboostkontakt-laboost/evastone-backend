#!/usr/bin/env bash
# Bootstrap świeżej VM (Oracle Cloud Always Free, Ubuntu 22.04/24.04, ARM/AMD).
# Uruchom na serwerze:  bash oracle-bootstrap.sh
set -euo pipefail

echo "==> pakiety bazowe"
sudo apt-get update -y
sudo apt-get install -y ca-certificates curl git

echo "==> Docker"
if ! command -v docker >/dev/null 2>&1; then
  curl -fsSL https://get.docker.com | sudo sh
  sudo usermod -aG docker "$USER"
fi

echo "==> firewall VM: Oracle Ubuntu ma domyślnie zamknięte 80/443 w iptables"
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 80 -j ACCEPT || true
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 443 -j ACCEPT || true
sudo netfilter-persistent save 2>/dev/null || sudo bash -c 'iptables-save > /etc/iptables/rules.v4' 2>/dev/null || true

echo "==> repo (repo jest PRYWATNE — użyj tokenu GitHub w URL, patrz DEPLOY-ORACLE.md)"
if [ ! -d evastone-backend ]; then
  echo "   git clone https://<GITHUB_TOKEN>@github.com/flowboostkontakt-laboost/evastone-backend.git"
  echo "   (uruchom clone ręcznie z tokenem, potem: cd evastone-backend)"
  exit 0
fi

cd evastone-backend
[ -f .env.production ] || cp .env.production.example .env.production

cat <<'NOTE'

==> ZOSTAŁO RĘCZNIE:
  1) nano .env.production   → uzupełnij DOMAIN, APP_URL, hasła DB, COMUP_LEGACY_PASSWORD, IP_PEPPER
  2) newgrp docker          → (albo wyloguj/zaloguj, żeby działać bez sudo)
  3) docker compose -f docker-compose.prod.yml up -d --build
  4) załaduj katalog:
     docker compose -f docker-compose.prod.yml exec app bash -lc \
       'B=$(php artisan comup:pull --source=dump | grep Batch | awk "{print \$2}"); \
        php artisan import:normalize $B && php artisan import:validate $B && php artisan import:publish'
NOTE
