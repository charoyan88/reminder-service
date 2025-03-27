#!/bin/bash
set -e

echo "========================================"
echo "  Reminder Service - Startup Script"
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
fi

echo "Building Docker images..."
docker-compose build

echo "Starting services..."
docker-compose up -d

echo "Waiting for services to be ready..."
sleep 10

echo "========================================"
echo "  Reminder Service is now running!"
echo "========================================"
echo ""
echo "Access the application at: http://localhost:8000"
echo "Access MailHog at: http://localhost:8025"
echo ""
echo "To stop the services, run: docker-compose down"
echo "========================================" 