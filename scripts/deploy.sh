#!/bin/bash
# =============================================================================
# JJA DEV LAB - Script de déploiement automatique
# Appelé par le webhook après un push sur master
# =============================================================================

set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
LOG_FILE="${APP_DIR}/var/log/deploy.log"
PHP_BIN=$(which php)
COMPOSER_BIN=$(which composer 2>/dev/null || echo "${APP_DIR}/composer.phar")

# Logging
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log "=========================================="
log "DÉPLOIEMENT DÉMARRÉ"
log "=========================================="

cd "$APP_DIR"
export APP_ENV=prod

# 1. Activer le mode maintenance
if [ -f "public/maintenance.html" ]; then
    log "[1/8] Mode maintenance déjà actif"
else
    log "[1/8] Activation du mode maintenance"
    cat > public/maintenance.html <<'MAINT'
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Maintenance</title>
<style>body{background:#05070a;color:#fff;font-family:monospace;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
.box{text-align:center;border:1px solid #1a2332;padding:3rem;max-width:400px}
h1{color:#00c6ff;font-size:1rem;text-transform:uppercase;letter-spacing:0.2em}
p{color:#64748b;font-size:0.7rem;text-transform:uppercase;letter-spacing:0.15em}</style></head>
<body><div class="box"><h1>Maintenance en cours</h1><p>Le système est en cours de mise à jour. Retour imminent.</p></div></body></html>
MAINT
fi

# 2. Pull du code
log "[2/8] git pull origin master"
git fetch origin master
git reset --hard origin/master

# 3. Install des dépendances (prod only)
log "[3/8] composer install --no-dev"
$COMPOSER_BIN install --no-dev --optimize-autoloader --no-interaction --no-progress --ignore-platform-req=ext-redis 2>&1 | tail -5 | tee -a "$LOG_FILE"

# 4. Migrations Doctrine
log "[4/8] Migrations Doctrine"
$PHP_BIN bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration 2>&1 | tee -a "$LOG_FILE"

# 5. Clear + Warmup cache
log "[5/8] Cache clear + warmup"
$PHP_BIN bin/console cache:clear --env=prod --no-debug 2>&1 | tee -a "$LOG_FILE"
$PHP_BIN bin/console cache:warmup --env=prod --no-debug 2>&1 | tee -a "$LOG_FILE"

# 6. Build Tailwind CSS
log "[6/8] Tailwind build"
$PHP_BIN bin/console tailwind:build --minify 2>&1 | tail -3 | tee -a "$LOG_FILE"

# 7. Compile assets
log "[7/8] Asset-map compile"
$PHP_BIN bin/console asset-map:compile 2>&1 | tee -a "$LOG_FILE"

# 8. Désactiver le mode maintenance
log "[8/8] Désactivation du mode maintenance"
rm -f public/maintenance.html

log "=========================================="
log "DÉPLOIEMENT TERMINÉ AVEC SUCCÈS"
log "=========================================="

exit 0
