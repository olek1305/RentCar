#!/bin/sh
set -e

echo "Waiting for database..."
while ! nc -z db 3306; do
    echo "Database not ready yet. Sleeping..."
    sleep 3
done

echo "Installing Composer dependencies..."
if [ ! -d "/var/www/vendor" ] || [ ! -f "/var/www/vendor/autoload.php" ]; then
    echo "Running composer install..."
    cd /var/www
    composer install --no-interaction --prefer-dist
else
    echo "Vendor directory exists, skipping composer install"
fi

echo "Installing NPM dependencies..."
if [ ! -d "/var/www/node_modules" ] || [ ! -d "/var/www/node_modules/vite" ]; then
    echo "Running npm install..."
    cd /var/www
    npm install
else
    echo "Node modules directory exists, skipping npm install"
fi

echo "Building initial frontend assets..."
if [ ! -f "/var/www/public/build/manifest.json" ]; then
    echo "Running initial build with npx vite build..."
    cd /var/www
    npx vite build
else
    echo "Build manifest exists, skipping initial build"
    echo "Note: The Vite service will handle hot-reloading"
fi

echo "Fixing permissions..."
chown -R laravel:laravel /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 777 /var/www/storage/logs 2>/dev/null || true

echo "Ensure log file exists and has proper permissions..."
touch /var/www/storage/logs/laravel.log 2>/dev/null || true
chown laravel:laravel /var/www/storage/logs/laravel.log 2>/dev/null || true
chmod 666 /var/www/storage/logs/laravel.log 2>/dev/null || true

echo "Generate APP_KEY if missing..."
if [ -f "/var/www/.env" ]; then
    if ! grep -q '^APP_KEY=base64:' /var/www/.env; then
      echo "Generating APP_KEY..."
      php artisan key:generate --force
    fi
else
    echo "Warning: .env file not found, skipping APP_KEY generation"
fi

echo "Running migrations..."
php artisan migrate --force 2>/dev/null || echo "Migrations failed or not needed"

echo "Clearing caches for development..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

echo "Starting PHP-FPM..."
exec php-fpm8.3 --nodaemonize
