#!/bin/sh
set -e

cd /var/www/html

# Render injecte $PORT dynamiquement — on l'applique au template nginx
: "${PORT:=10000}"
export PORT
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

mkdir -p var/cache var/log public/uploads
chown -R www-data:www-data var public/uploads
chmod -R u+rwX var public/uploads

# Création/MAJ du schéma à partir des entités (les migrations existantes sont MySQL-only)
# Lancée en arrière-plan pour ne pas bloquer le boot — le healthcheck doit répondre vite.
if [ -n "${DATABASE_URL}" ]; then
    su-exec www-data php bin/console doctrine:schema:update --force --complete --env=prod --no-interaction >/proc/1/fd/1 2>/proc/1/fd/2 &
fi

exec "$@"
