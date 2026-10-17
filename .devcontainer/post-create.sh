#!/usr/bin/env bash
set -euo pipefail

echo "=== JJA DEV LABS — initialisation du devcontainer MariaDB ==="

if [ ! -f composer.json ]; then
  echo "composer.json introuvable : le devcontainer doit être ouvert à la racine du projet."
  exit 1
fi

echo "=== Installation des dépendances Composer ==="
composer install --prefer-dist --no-interaction --no-progress

echo "=== Installation des assets Symfony ==="
php bin/console assets:install --no-interaction || true
php bin/console importmap:install --no-interaction || true

echo "=== Création de la base de données si nécessaire ==="
php bin/console doctrine:database:create --if-not-exists --no-interaction || true

echo "=== Application des migrations ==="
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || true

echo "=== Nettoyage du cache ==="
php bin/console cache:clear --no-warmup || true

echo "=== Environnement prêt ==="
echo "Symfony : http://localhost:8000"
echo "Mailpit : http://localhost:8025"
echo "Tests   : php bin/phpunit"
echo "PHPStan : vendor/bin/phpstan analyse"