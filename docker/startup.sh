#!/bin/bash
set -e

cd /var/www/reminder-service

# Install dependencies if needed
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
    echo "Installing dependencies..."
    composer install --no-interaction --optimize-autoloader
fi

# Generate application key if not set
if [ -z "$(php artisan key:status | grep "Application key")" ]; then
    echo "Generating application key..."
    php artisan key:generate
fi

# Wait for database to be ready
echo "Waiting for database connection..."
max_retries=30
counter=0
while ! php artisan db:monitor > /dev/null 2>&1; do
    counter=$((counter+1))
    if [ $counter -gt $max_retries ]; then
        echo "Error: Failed to connect to database after $max_retries attempts"
        echo "Database configuration:"
        echo "HOST: $DB_HOST"
        echo "PORT: $DB_PORT"
        echo "DATABASE: $DB_DATABASE"
        echo "USERNAME: $DB_USERNAME"
        exit 1
    fi
    echo "Waiting for database connection... ($counter/$max_retries)"
    sleep 3
done
echo "Database connection successful!"

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Run seeders if DB_SEED is true
if [ "${DB_SEED}" = "true" ]; then
    echo "Running database seeders..."
    php artisan db:seed --force
fi

# Clear and rebuild cache
echo "Clearing cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize
echo "Optimizing application..."
php artisan optimize

# Set proper permissions
chown -R www-data:www-data storage bootstrap/cache

echo "Startup completed successfully!"

# Start PHP-FPM
exec php-fpm --nodaemonize 