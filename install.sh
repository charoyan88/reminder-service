#!/bin/bash

echo "Installing Reminder Service..."

# Create required directories
mkdir -p data/mariadb
mkdir -p data/redis
mkdir -p docker/cron

# Copy .env file if not exists
if [ ! -f .env ]; then
    cp .env.example .env
    echo ".env file created."
else
    echo ".env file already exists."
fi

# Generate application key
docker-compose run --rm app php artisan key:generate
echo "Application key generated."

# Build and start services
docker-compose up -d
echo "Docker services started."

# Wait for database to be ready
echo "Waiting for database to initialize..."
sleep 10

# Run migrations
docker-compose exec app php artisan migrate --seed
echo "Database migrations completed."

# Optimize the application
docker-compose exec app php artisan optimize
echo "Application optimized."

echo "Installation completed successfully!"
echo "You can access the application at: http://localhost"
echo "API endpoints are available at: http://localhost/api"
echo "Email testing UI is available at: http://localhost:8025" 