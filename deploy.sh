#!/bin/bash

# Deploy Script untuk KMP Laravel Application
# Usage: ./deploy.sh

echo "🚀 Starting KMP Application Deployment..."

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Copy .env.docker to .env if .env doesn't exist
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.docker..."
    cp .env.docker .env
else
    echo "✅ .env file already exists"
fi

# Stop existing containers
echo "🛑 Stopping existing containers..."
docker-compose down

# Build and start containers
echo "🔨 Building Docker images..."
docker-compose build --no-cache

echo "🚀 Starting containers..."
docker-compose up -d

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 20

# Generate application key if not set
echo "🔑 Generating application key..."
docker-compose exec -T app php artisan key:generate

# Run migrations
echo "📊 Running database migrations..."
docker-compose exec -T app php artisan migrate --force

# Run seeders (optional - uncomment if needed)
# echo "🌱 Running database seeders..."
# docker-compose exec -T app php artisan db:seed --force

# Clear and cache config
echo "🧹 Clearing cache..."
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan cache:clear
docker-compose exec -T app php artisan view:clear
docker-compose exec -T app php artisan route:clear

echo "📦 Caching config..."
docker-compose exec -T app php artisan config:cache
docker-compose exec -T app php artisan route:cache
docker-compose exec -T app php artisan view:cache

# Create storage link
echo "🔗 Creating storage link..."
docker-compose exec -T app php artisan storage:link

# Set permissions
echo "🔒 Setting permissions..."
docker-compose exec -T app chown -R www-data:www-data /var/www/html/storage
docker-compose exec -T app chown -R www-data:www-data /var/www/html/bootstrap/cache
docker-compose exec -T app chmod -R 775 /var/www/html/storage
docker-compose exec -T app chmod -R 775 /var/www/html/bootstrap/cache

echo ""
echo "✅ Deployment completed successfully!"
echo ""
echo "📋 Application Information:"
echo "   - Application URL: http://localhost:8000"
echo "   - PHPMyAdmin URL: http://localhost:8080"
echo "   - Database: kmp_db"
echo ""
echo "🔍 Useful commands:"
echo "   - View logs: docker-compose logs -f"
echo "   - Stop containers: docker-compose down"
echo "   - Restart containers: docker-compose restart"
echo "   - Access app container: docker-compose exec app bash"
echo ""
