#!/bin/sh
set -e

cd /var/www/html

# Render injecte $PORT dynamiquement — on l'applique au template nginx
: "${PORT:=10000}"
export PORT
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

mkdir -p var/cache var/log public/uploads
chown -R www-data:www-data var public/uploads || true

# Cache prod
php bin/console cache:clear --env=prod --no-debug || true
php bin/console cache:warmup --env=prod --no-debug || true

# Création/MAJ du schéma à partir des entités (les migrations existantes sont MySQL-only)
if [ -n "${DATABASE_URL}" ]; then
    php bin/console doctrine:schema:update --force --complete --env=prod || true
fi

exec "$@"
