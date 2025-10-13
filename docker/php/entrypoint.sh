#!/bin/sh
set -e

echo "Waiting for database..."
while ! nc -z db 3306; do
    echo "Database not ready yet. Sleeping..."
    sleep 3
done

echo "Database is ready!"

echo "Generate APP_KEY if missing..."
if ! grep -q '^APP_KEY=base64:' /var/www/.env; then
  echo "Generating APP_KEY..."
  php artisan key:generate --force
fi

echo "Running migrations..."
php artisan migrate --force

echo "Starting Clear & cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting PHP-FPM..."
exec php-fpm8.3 --nodaemonize
