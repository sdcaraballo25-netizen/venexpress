#!/bin/sh
# Arranque del contenedor (Render). Prepara la configuración, migra la
# base de datos y levanta nginx + php-fpm + cola + scheduler.
set -e

cd /var/www/html

artisan() {
    runuser -u www-data -- php artisan "$@"
}

# URL pública: Render la publica en RENDER_EXTERNAL_URL.
if [ -z "${APP_URL:-}" ] && [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

# APP_KEY: Render genera un valor aleatorio sin el formato de Laravel
# ("base64:" + 32 bytes). Se deriva una llave válida de ese valor; es
# estable: el mismo valor siempre da la misma llave.
if [ -z "${APP_KEY:-}" ]; then
    echo "[venexpress] Falta la variable APP_KEY." >&2
    exit 1
fi
case "$APP_KEY" in
    base64:*) ;;
    *) APP_KEY="base64:$(php -r 'echo base64_encode(hash("sha256", getenv("APP_KEY"), true));')"
       export APP_KEY ;;
esac

sed "s/__PORT__/${PORT:-10000}/" /etc/nginx/venexpress.conf.template > /etc/nginx/nginx.conf

artisan config:cache
artisan route:cache
artisan event:cache

# La base de datos puede tardar unos segundos en aceptar conexiones.
attempt=1
until artisan migrate --force --no-interaction; do
    if [ "$attempt" -ge 10 ]; then
        echo "[venexpress] No se pudo migrar la base de datos." >&2
        exit 1
    fi
    attempt=$((attempt + 1))
    echo "[venexpress] Reintentando la migración en 5 s ($attempt/10)..."
    sleep 5
done

if [ -n "${ADMIN_EMAIL:-}" ]; then
    artisan venexpress:create-admin --email="$ADMIN_EMAIL" --name="${ADMIN_NAME:-Administrador}" --no-interaction \
        || echo "[venexpress] No se creó el administrador inicial (revisa ADMIN_EMAIL / ADMIN_PASSWORD)." >&2
fi

# Tasa BCV al arrancar (el scheduler solo la consulta en horario hábil).
(artisan bcv:sync || true) &

exec supervisord -c /etc/supervisor/supervisord.conf
