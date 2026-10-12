#!/bin/sh
set -e

# Ensure SQLite database exists
mkdir -p /var/www/html/database
touch /var/www/html/database/database.sqlite
chmod 777 /var/www/html/database/database.sqlite

# Ensure storage directories exist
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate key if not present
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run database migrations and seed demo data
php artisan migrate --force --seed

# Start Laravel server on Render's assigned PORT
PORT="${PORT:-10000}"
echo "ValetFlow starting on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port="$PORT"
