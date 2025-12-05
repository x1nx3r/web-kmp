#!/bin/bash
set -e

# Fix storage permissions at runtime
chown -R unit:unit /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear and rebuild caches with proper permissions
php /var/www/html/artisan config:clear
php /var/www/html/artisan cache:clear
php /var/www/html/artisan view:clear
php /var/www/html/artisan route:clear

# Optionally rebuild caches for production
if [ "$APP_ENV" = "production" ]; then
    php /var/www/html/artisan config:cache
    php /var/www/html/artisan route:cache
    php /var/www/html/artisan view:cache
fi

# Start Unit
exec unitd --no-daemon
