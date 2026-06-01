#!/bin/sh
set -e

cd /var/www/html

# Permissions du dossier var (cache/logs) au boot
mkdir -p var/cache var/log public/uploads
chown -R www-data:www-data var public/uploads || true

# Joue les migrations Doctrine au démarrage si la base est joignable
if [ -n "${DATABASE_URL}" ]; then
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=prod || true
fi

exec "$@"
