#!/bin/sh
set -e

# Fix ownership/permissions
chown -R www-data:www-data /var/www/html || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Optional: run migrations if requested
if [ "$RUN_MIGRATIONS" = "1" ] || [ "$RUN_MIGRATIONS" = "true" ]; then
  echo "Running migrations..."
  php artisan migrate --force || true
fi

# Start Apache in foreground
exec apache2-foreground
