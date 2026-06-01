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
    (
        su-exec www-data php bin/console doctrine:schema:update --force --complete --env=prod --no-interaction >/proc/1/fd/1 2>/proc/1/fd/2
        
        # Automate fixtures loading for free Render tier where SSH/Shell is restricted
        cat << 'EOF' > var/check_db.php
<?php
require __DIR__.'/../vendor/autoload.php';
$kernel = new \App\Kernel('prod', false);
$kernel->boot();
echo $kernel->getContainer()->get('doctrine')->getRepository(\App\Entity\User::class)->count([]);
EOF
        USER_COUNT=$(su-exec www-data php var/check_db.php 2>/dev/null || echo "0")
        if [ "$USER_COUNT" = "0" ] || [ "$USER_COUNT" = "" ]; then
            echo "Database is empty. Loading fixtures automatically..." >/proc/1/fd/1
            su-exec www-data php bin/console doctrine:fixtures:load -n --env=prod >/proc/1/fd/1 2>/proc/1/fd/2
        fi
    ) &
fi

exec "$@"
