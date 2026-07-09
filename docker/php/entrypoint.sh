#!/bin/sh
# ============================================================
# Entrypoint de producción.
# El código y vendor ya vienen horneados en la imagen. Aquí solo
# preparamos lo que depende del ENTORNO en RUNTIME (inyectado por
# docker-compose), no del build.
# ============================================================
set -e

cd /var/www/html

# El volumen de storage puede venir vacío en el primer arranque.
mkdir -p \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true

# Cachés de Laravel con el APP_KEY / DB / etc. reales del contenedor.
# Se usa "|| true" para que un fallo de caché no impida arrancar la app.
php artisan config:cache || true
php artisan route:cache  || true
php artisan view:cache   || true
php artisan event:cache  || true

exec "$@"
