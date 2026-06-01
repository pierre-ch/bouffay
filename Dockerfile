FROM php:8.2-fpm-alpine AS base

RUN apk add --no-cache \
        git curl unzip bash gettext su-exec \
        nginx supervisor postgresql-dev \
        libpng-dev libjpeg-turbo-dev libwebp-dev libzip-dev icu-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo pdo_pgsql gd zip intl opcache mbstring bcmath \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

FROM base AS build

COPY composer.json composer.lock symfony.lock ./
COPY bin ./bin
COPY config ./config
COPY public ./public
COPY src ./src
COPY templates ./templates
COPY translations ./translations
COPY migrations ./migrations
COPY .env ./.env

ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader --no-scripts \
    && composer dump-autoload --optimize --classmap-authoritative --no-dev \
    && mkdir -p var/cache var/log \
    && php bin/console cache:clear --env=prod --no-debug \
    && php bin/console cache:warmup --env=prod --no-debug --no-optional-warmers

FROM base AS final

COPY --from=build /var/www/html /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/render/nginx.conf.template /etc/nginx/nginx.conf.template
COPY docker/render/php-fpm.conf /usr/local/etc/php-fpm.d/zz-render.conf
COPY docker/render/supervisord.conf /etc/supervisord.conf
COPY docker/render/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p /run/nginx /var/log/supervisor \
    && chown -R www-data:www-data /var/www/html/var

ENV PORT=10000
EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
