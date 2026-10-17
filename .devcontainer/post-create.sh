#!/usr/bin/env bash
set -euo pipefail

echo "=== JJA DEV LABS — initialisation du devcontainer ==="

if [ ! -f composer.json ]; then
  echo "composer.json introuvable : vérifie que le devcontainer est ouvert à la racine du dépôt."
  exit 1
fi

echo "=== Installation des dépendances PHP ==="
if [ -f composer.lock ]; then
  composer install --prefer-dist --no-interaction --no-progress
else
  composer install --prefer-dist --no-interaction --no-progress
fi

echo "=== Installation des assets Symfony ==="
php bin/console assets:install --no-interaction || true
php bin/console importmap:install --no-interaction || true

echo "=== Vérification de la base de données ==="
if php bin/console doctrine:database:create --if-not-exists --no-interaction; then
  echo "Base de données prête."
else
  echo "Impossible de créer la base : vérifie DATABASE_URL et l'état du service PostgreSQL."
fi

echo "=== Application des migrations ==="
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true

echo "=== Cache Symfony ==="
php bin/console cache:clear --no-warmup || true

echo "=== Environnement prêt ==="
echo "Symfony : symfony server:start"
echo "Tests   : php bin/phpunit"
echo "Analyse : vendor/bin/phpstan analyse"