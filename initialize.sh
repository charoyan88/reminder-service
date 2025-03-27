#!/bin/bash
set -e

echo "========================================"
echo "  Reminder Service - Initialization"
echo "========================================"

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "Error: Docker is not installed or not in PATH"
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo "Error: Docker Compose is not installed or not in PATH"
    exit 1
fi

# Check if .env file exists
if [ ! -f ".env" ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
    
    # Generate a random string for the APP_KEY
    APP_KEY=$(openssl rand -base64 32)
    sed -i "s/APP_KEY=/APP_KEY=base64:$APP_KEY/g" .env
    
    # Set DB_SEED to true to ensure the database is seeded
    sed -i "s/DB_SEED=.*/DB_SEED=true/g" .env
    # If DB_SEED isn't in the file, add it
    if ! grep -q "DB_SEED" .env; then
        echo "DB_SEED=true" >> .env
    fi
fi

echo "Building Docker images..."
docker-compose build

echo "Creating required directories..."
mkdir -p data/mysql data/redis storage/logs bootstrap/cache

echo "Setting proper permissions..."
chmod -R 777 storage bootstrap/cache

echo "Starting services for initial setup..."
docker-compose up -d

echo "Waiting for services to be ready..."
sleep 15

echo "Running database migrations and seeding..."
docker-compose exec app php artisan migrate --force --seed

echo "Clearing caches..."
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan route:clear

echo "========================================"
echo "  Initialization Complete!"
echo "========================================"
echo ""
echo "The Reminder Service is now running at: http://localhost:8000"
echo "Access MailHog at: http://localhost:8025"
echo ""
echo "Default user:"
echo "Email: test@example.com"
echo "Password: password"
echo ""
echo "To stop the services, run: docker-compose down"
echo "========================================" 