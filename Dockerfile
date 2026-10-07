# syntax=docker/dockerfile:1
#
# Venexpress en un solo contenedor, pensado para Render (plan gratis):
# nginx + php-fpm + worker de colas + scheduler, bajo supervisord.
# Ver docs/DESPLIEGUE_RENDER.md.

# --- Dependencias PHP (sin las de desarrollo) --------------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist \
        --no-interaction --no-progress --ignore-platform-reqs

# --- Assets (Vite + Tailwind) ------------------------------------------------
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
# Tailwind también escanea las vistas de paginación de Laravel.
COPY --from=vendor /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views \
        ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

# --- Imagen final ------------------------------------------------------------
# Ubuntu 24.04 trae PHP 8.3 (la misma versión que se usa en desarrollo).
FROM ubuntu:24.04

ENV DEBIAN_FRONTEND=noninteractive \
    APP_DIR=/var/www/html \
    PORT=10000

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates nginx supervisor unzip \
        php8.3-fpm php8.3-cli php8.3-pgsql php8.3-mysql php8.3-gd php8.3-zip \
        php8.3-intl php8.3-bcmath php8.3-mbstring php8.3-xml php8.3-curl php8.3-opcache \
    && rm -rf /var/lib/apt/lists/* \
    && rm -f /etc/nginx/sites-enabled/default

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

COPY docker/php.ini /etc/php/8.3/fpm/conf.d/99-venexpress.ini
COPY docker/php.ini /etc/php/8.3/cli/conf.d/99-venexpress.ini
COPY docker/php-fpm-pool.conf /etc/php/8.3/fpm/pool.d/www.conf
COPY docker/nginx.conf.template /etc/nginx/venexpress.conf.template
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/venexpress-entrypoint

RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && mkdir -p storage/app/public storage/app/private storage/framework/cache/data \
        storage/framework/sessions storage/framework/views storage/logs bootstrap/cache /run/php \
    && ln -sfn ../storage/app/public public/storage \
    && php artisan view:cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x /usr/local/bin/venexpress-entrypoint

EXPOSE 10000

ENTRYPOINT ["venexpress-entrypoint"]
