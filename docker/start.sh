#!/bin/sh

echo "=== Container starting ==="
echo "Date: $(date)"
echo "PHP version: $(php -v | head -1)"
echo "Working dir: $(pwd)"

cd /var/www/html

# Ensure storage directories exist and are writable
mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Verify key files exist
echo "=== Checking files ==="
ls -la public/index.php 2>&1 || echo "WARNING: public/index.php not found!"
ls -la vendor/autoload.php 2>&1 || echo "WARNING: vendor/autoload.php not found!"

# Clear old caches first, then regenerate
echo "=== Clearing old caches ==="
php artisan config:clear 2>&1 || echo "config:clear failed"
php artisan route:clear 2>&1 || echo "route:clear failed"
php artisan view:clear 2>&1 || echo "view:clear failed"

# Debug: show actual DB config being used
echo "=== DB Socket config ==="
php artisan tinker --execute="echo config('database.connections.mysql.unix_socket');" 2>&1 || echo "tinker failed"

# Generate optimized caches
echo "=== Caching config ==="
php artisan config:cache 2>&1 || echo "config:cache failed (non-fatal)"
echo "=== Caching routes ==="
php artisan route:cache 2>&1 || echo "route:cache failed (non-fatal)"
echo "=== Caching views ==="
php artisan view:cache 2>&1 || echo "view:cache failed (non-fatal)"

# Verify PHP-FPM config
echo "=== PHP-FPM config ==="
php-fpm -t 2>&1 || echo "PHP-FPM config test failed!"

# Verify Nginx config
echo "=== Nginx config ==="
nginx -t 2>&1 || echo "Nginx config test failed!"

# Create nginx PID directory
mkdir -p /run/nginx

echo "=== Starting Supervisor (nginx + php-fpm) ==="

# Start supervisor (nginx + php-fpm)
exec /usr/bin/supervisord -c /etc/supervisord.conf
