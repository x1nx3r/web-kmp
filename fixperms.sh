#!/bin/sh

# Ensure Laravel storage dirs exist
mkdir -p /var/www/html/storage/framework/{cache,sessions,views}
mkdir -p /var/www/html/bootstrap/cache

# Fix permissions for volumes
chown -R unit:unit /var/www/html/storage
chown -R unit:unit /var/www/html/bootstrap/cache

chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Start Unit
exec "$@"
