#!/bin/bash
# =============================================================================
# JJA DEV LAB - PREMIER DÉPLOIEMENT sur OVH
# À exécuter UNE SEULE FOIS sur le serveur OVH
# =============================================================================
#
# USAGE:
#   1. Se connecter en SSH au serveur OVH
#   2. Cloner le repo :
#      git clone git@github.com:JJADEV/JJA_DEV_LABS.git /chemin/vers/site
#   3. Copier et remplir le .env.prod.local :
#      cp .env.prod.local.example .env.prod.local
#      nano .env.prod.local
#   4. Lancer ce script :
#      chmod +x scripts/first-deploy.sh
#      bash scripts/first-deploy.sh
#
# =============================================================================

set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")/.." && pwd)"
PHP_BIN=$(which php)
COMPOSER_BIN=$(which composer 2>/dev/null || echo "")

echo "============================================"
echo "  JJA DEV LAB - Premier déploiement"
echo "============================================"
echo ""

cd "$APP_DIR"

# Vérifier le .env.local (config prod)
if [ ! -f ".env.local" ]; then
    if [ -f ".env.prod.local" ]; then
        echo "[INFO] Renommage .env.prod.local → .env.local"
        mv .env.prod.local .env.local
    else
        echo "ERREUR: .env.local introuvable !"
        echo "Copiez .env.prod.local.example vers .env.local et remplissez les valeurs."
        exit 1
    fi
fi

echo "[OK] .env.local trouvé"

# Installer Composer si absent
if [ -z "$COMPOSER_BIN" ]; then
    echo "[INFO] Composer non trouvé, téléchargement..."
    curl -sS https://getcomposer.org/installer | $PHP_BIN -- --install-dir="$APP_DIR" --filename=composer.phar
    COMPOSER_BIN="$APP_DIR/composer.phar"
fi

# Forcer l'environnement prod pour que les auto-scripts Composer
# ne chargent pas les bundles dev (DebugBundle, WebProfilerBundle, etc.)
export APP_ENV=prod

# 1. Nettoyage des fichiers inutiles en production
echo "[1/7] Nettoyage fichiers dev/test..."
rm -rf tests/ mockup/ phpstan.dist.neon phpunit.dist.xml

# 2. Dépendances
echo "[2/7] Installation des dépendances..."
$COMPOSER_BIN install --no-dev --optimize-autoloader --no-interaction --ignore-platform-req=ext-redis

# 3. Créer la base de données si elle n'existe pas
echo "[3/7] Vérification de la base de données..."
$PHP_BIN bin/console doctrine:database:create --if-not-exists --env=prod

# 4. Migrations
echo "[4/7] Exécution des migrations..."
$PHP_BIN bin/console doctrine:migrations:migrate --no-interaction --env=prod

# 5. Cache
echo "[5/7] Construction du cache prod..."
$PHP_BIN bin/console cache:clear --env=prod --no-debug
$PHP_BIN bin/console cache:warmup --env=prod --no-debug

# 6. Permissions
echo "[6/7] Permissions des répertoires..."
chmod -R 775 var/
chmod +x scripts/deploy.sh

echo ""
echo "============================================"
echo "  PREMIER DÉPLOIEMENT TERMINÉ !"
echo "============================================"
echo ""
echo "Prochaines étapes :"
echo "  1. Configurer le vhost Apache/Nginx vers public/"
echo "  2. Configurer les secrets GitHub Actions (Settings > Secrets) :"
echo "     OVH_SSH_HOST, OVH_SSH_USER, OVH_SSH_KEY, OVH_DEPLOY_PATH"
echo "  3. Les prochains push sur master déclencheront le déploiement via SSH"
echo ""
