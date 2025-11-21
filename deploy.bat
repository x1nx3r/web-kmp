@echo off
REM Deploy Script untuk KMP Laravel Application (Windows)
REM Usage: deploy.bat

echo Starting KMP Application Deployment...

REM Check if Docker is installed
docker --version >nul 2>&1
if errorlevel 1 (
    echo Docker is not installed. Please install Docker first.
    exit /b 1
)

REM Check if Docker Compose is installed
docker-compose --version >nul 2>&1
if errorlevel 1 (
    echo Docker Compose is not installed. Please install Docker Compose first.
    exit /b 1
)

REM Copy .env.docker to .env if .env doesn't exist
if not exist .env (
    echo Creating .env file from .env.docker...
    copy .env.docker .env
) else (
    echo .env file already exists
)

REM Stop existing containers
echo Stopping existing containers...
docker-compose down

REM Build and start containers
echo Building Docker images...
docker-compose build --no-cache

echo Starting containers...
docker-compose up -d

REM Wait for MySQL to be ready
echo Waiting for MySQL to be ready...
timeout /t 20 /nobreak

REM Generate application key if not set
echo Generating application key...
docker-compose exec -T app php artisan key:generate

REM Run migrations
echo Running database migrations...
docker-compose exec -T app php artisan migrate --force

REM Run seeders (optional - uncomment if needed)
REM echo Running database seeders...
REM docker-compose exec -T app php artisan db:seed --force

REM Clear and cache config
echo Clearing cache...
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan cache:clear
docker-compose exec -T app php artisan view:clear
docker-compose exec -T app php artisan route:clear

echo Caching config...
docker-compose exec -T app php artisan config:cache
docker-compose exec -T app php artisan route:cache
docker-compose exec -T app php artisan view:cache

REM Create storage link
echo Creating storage link...
docker-compose exec -T app php artisan storage:link

REM Set permissions
echo Setting permissions...
docker-compose exec -T app chown -R www-data:www-data /var/www/html/storage
docker-compose exec -T app chown -R www-data:www-data /var/www/html/bootstrap/cache
docker-compose exec -T app chmod -R 775 /var/www/html/storage
docker-compose exec -T app chmod -R 775 /var/www/html/bootstrap/cache

echo.
echo Deployment completed successfully!
echo.
echo Application Information:
echo    - Application URL: http://localhost:8000
echo    - PHPMyAdmin URL: http://localhost:8080
echo    - Database: kmp_db
echo.
echo Useful commands:
echo    - View logs: docker-compose logs -f
echo    - Stop containers: docker-compose down
echo    - Restart containers: docker-compose restart
echo    - Access app container: docker-compose exec app bash
echo.

pause
