#!/bin/sh
set -e

echo "Waiting for database..."
while ! nc -z db 3306; do
    echo "Database not ready yet. Sleeping..."
    sleep 3
done

echo "Database is ready!"

# Generate APP_KEY if missing
if ! grep -q '^APP_KEY=base64:' /var/www/.env; then
  echo "Generating APP_KEY..."
  php artisan key:generate --force
fi

# # Populate public volume if empty
# if [ -z "$(ls -A /var/www/public)" ]; then
#   echo "Populating /var/www/public from template..."
#   cp -r /template/public/* /var/www/public/
#   chmod -R 775 /var/www/public
# fi

echo "Running migrations..."
php artisan migrate --force

# Clear & cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start PHP-FPM
exec /usr/local/sbin/php-fpm --nodaemonize
