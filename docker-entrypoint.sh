
#!/bin/bash

# Don't exit on error - we want Unit to start even if artisan fails
set +e

echo "==> Fixing storage permissions..."
chown -R unit:unit /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

echo "==> Clearing caches..."
php artisan config:clear 2>&1 || echo "Warning: config:clear failed"
php artisan cache:clear 2>&1 || echo "Warning: cache:clear failed"
php artisan view:clear 2>&1 || echo "Warning: view:clear failed"
php artisan route:clear 2>&1 || echo "Warning: route:clear failed"

# Optionally rebuild caches for production
if [ "$APP_ENV" = "production" ]; then
    echo "==> Building production caches..."
    php artisan config:cache 2>&1 || echo "Warning: config:cache failed"
    php artisan route:cache 2>&1 || echo "Warning: route:cache failed"
    php artisan view:cache 2>&1 || echo "Warning: view:cache failed"
fi

echo "==> Starting Unit..."
exec unitd --no-daemon
