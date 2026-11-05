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
# PHP 8.5 (última versión estable). Ubuntu 24.04 solo trae PHP 8.3, así
# que se usa el repositorio de Ondřej Surý (ppa:ondrej/php), el estándar
# para versiones actuales de PHP en Ubuntu/Debian. Para cambiar de
# versión basta con PHP_VERSION (rutas de config y php-fpm la usan).
FROM ubuntu:24.04

ARG PHP_VERSION=8.5

ENV DEBIAN_FRONTEND=noninteractive \
    APP_DIR=/var/www/html \
    PORT=10000 \
    PHP_VERSION=${PHP_VERSION}

RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates gnupg software-properties-common \
    && add-apt-repository -y ppa:ondrej/php \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        nginx supervisor unzip \
        php${PHP_VERSION}-fpm php${PHP_VERSION}-cli php${PHP_VERSION}-pgsql php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-gd php${PHP_VERSION}-zip php${PHP_VERSION}-intl php${PHP_VERSION}-bcmath \
        php${PHP_VERSION}-mbstring php${PHP_VERSION}-xml php${PHP_VERSION}-curl \
    && (apt-get install -y --no-install-recommends php${PHP_VERSION}-opcache || true) \
    && apt-get purge -y --auto-remove software-properties-common gnupg \
    && rm -rf /var/lib/apt/lists/* \
    && rm -f /etc/nginx/sites-enabled/default \
    && ln -sf /usr/sbin/php-fpm${PHP_VERSION} /usr/local/sbin/php-fpm

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

COPY docker/php.ini /etc/php/${PHP_VERSION}/fpm/conf.d/99-venexpress.ini
COPY docker/php.ini /etc/php/${PHP_VERSION}/cli/conf.d/99-venexpress.ini
COPY docker/php-fpm-pool.conf /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf
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
