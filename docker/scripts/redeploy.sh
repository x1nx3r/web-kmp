#!/bin/sh
set -e

echo "Redeploy hook placeholder"
# Add redeploy steps here (clear caches, restart services, warm caches)
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
