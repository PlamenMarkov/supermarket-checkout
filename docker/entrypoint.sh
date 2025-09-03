#!/usr/bin/env bash
set -e

echo "[entrypoint] Starting Symfony container (APP_ENV=${APP_ENV:-dev})"

# Ensure Symfony runtime directory exists and is writable
mkdir -p /var/www/html/var
chown -R www-data:www-data /var/www/html/var || true

# Run composer install if vendor is missing
if [ ! -d "/var/www/html/vendor" ]; then
  echo "[entrypoint] vendor/ not found → running composer install"
  composer install --no-interaction --prefer-dist
fi

# Run migrations
attempt=1
while true; do
  if php bin/console doctrine:migrations:migrate -n; then break; fi
  [ $attempt -ge 10 ] && break;
  echo "[entrypoint] Retry $attempt…"
  attempt=$((attempt+1))
  sleep 2
done

# Start PHP-FPM and Nginx
echo "[entrypoint] Starting PHP-FPM + Nginx"
php-fpm -D
exec nginx -g "daemon off;"