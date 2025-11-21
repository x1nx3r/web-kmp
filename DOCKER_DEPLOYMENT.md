# KMP Laravel Application - Docker Deployment

Panduan lengkap untuk deploy aplikasi KMP menggunakan Docker dengan MySQL sebagai database.

## 📋 Prerequisites

Sebelum memulai, pastikan server Anda sudah terinstall:
- Docker (versi 20.10+)
- Docker Compose (versi 1.29+)

### Install Docker di Ubuntu/Debian:
```bash
# Update package index
sudo apt-get update

# Install dependencies
sudo apt-get install -y apt-transport-https ca-certificates curl software-properties-common

# Add Docker's official GPG key
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Add Docker repository
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Verify installation
docker --version
docker compose version
```

## 🚀 Quick Start

### 1. Clone atau Upload Project ke Server

```bash
# Clone dari repository (jika menggunakan Git)
git clone <repository-url> kmp-app
cd kmp-app

# Atau upload project ke server menggunakan FTP/SCP
```

### 2. Konfigurasi Environment

```bash
# Copy file .env.docker ke .env
cp .env.docker .env

# Edit .env sesuai kebutuhan
nano .env
```

Edit konfigurasi berikut di file `.env`:
```env
APP_NAME=KMP
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com

DB_DATABASE=kmp_db
DB_USERNAME=kmp_user
DB_PASSWORD=your_secure_password

APP_PORT=8000
PHPMYADMIN_PORT=8080

# Cloudflare Turnstile CAPTCHA
TURNSTILE_SITE_KEY=0x4AAAAAACCNqkZlDjHaPa3X
TURNSTILE_SECRET_KEY=0x4AAAAAACCNqmljPjeXdTRQyKugv6pIskg
```

### 3. Deploy Aplikasi

**Untuk Linux/Mac:**
```bash
# Berikan permission execute pada script
chmod +x deploy.sh

# Jalankan deployment script
./deploy.sh
```

**Untuk Windows:**
```cmd
deploy.bat
```

### 4. Akses Aplikasi

Setelah deployment selesai, akses aplikasi di:
- **Aplikasi**: http://localhost:8000 atau http://your-server-ip:8000
- **PHPMyAdmin**: http://localhost:8080 atau http://your-server-ip:8080

## 📦 Struktur Docker

Project ini menggunakan 3 container Docker:

1. **app** - Laravel Application (PHP 8.2 + Apache)
2. **mysql** - MySQL 8.0 Database
3. **phpmyadmin** - PHPMyAdmin untuk management database

## 🔧 Manual Deployment Steps

Jika ingin deploy manual tanpa script:

### 1. Build dan Start Container

```bash
# Build images
docker-compose build --no-cache

# Start containers
docker-compose up -d

# Check container status
docker-compose ps
```

### 2. Generate Application Key

```bash
docker-compose exec app php artisan key:generate
```

### 3. Run Database Migrations

```bash
docker-compose exec app php artisan migrate --force
```

### 4. Run Database Seeders (Opsional)

```bash
docker-compose exec app php artisan db:seed --force
```

### 5. Clear dan Cache Config

```bash
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan route:clear

docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

### 6. Create Storage Link

```bash
docker-compose exec app php artisan storage:link
```

### 7. Set Permissions

```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/bootstrap/cache
```

## 🛠️ Useful Commands

### Container Management

```bash
# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f app
docker-compose logs -f mysql

# Stop containers
docker-compose down

# Restart containers
docker-compose restart

# Restart specific service
docker-compose restart app
```

### Access Container Shell

```bash
# Access app container
docker-compose exec app bash

# Access MySQL container
docker-compose exec mysql bash

# Connect to MySQL
docker-compose exec mysql mysql -u kmp_user -p kmp_db
```

### Laravel Artisan Commands

```bash
# Run artisan commands
docker-compose exec app php artisan [command]

# Examples:
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan queue:work
```

### Database Operations

```bash
# Backup database
docker-compose exec mysql mysqldump -u kmp_user -p kmp_db > backup.sql

# Restore database
docker-compose exec -T mysql mysql -u kmp_user -p kmp_db < backup.sql

# Access MySQL CLI
docker-compose exec mysql mysql -u kmp_user -p
```

## 🔒 Security Recommendations

1. **Change Default Passwords**
   - Edit `.env` dan ubah `DB_PASSWORD` dengan password yang kuat
   - Ubah `MYSQL_ROOT_PASSWORD` di `docker-compose.yml`

2. **Disable Debug Mode**
   ```env
   APP_DEBUG=false
   APP_ENV=production
   ```

3. **Use HTTPS**
   - Setup reverse proxy (Nginx/Traefik) dengan SSL certificate
   - Update `APP_URL` dengan https://

4. **Firewall Configuration**
   ```bash
   # Allow only necessary ports
   sudo ufw allow 8000/tcp
   sudo ufw allow 8080/tcp
   sudo ufw enable
   ```

5. **Disable PHPMyAdmin in Production** (Opsional)
   - Comment service `phpmyadmin` di `docker-compose.yml`

## 🌐 Production Setup dengan Nginx Reverse Proxy

Untuk production, disarankan menggunakan Nginx sebagai reverse proxy:

### 1. Install Nginx

```bash
sudo apt-get install nginx
```

### 2. Create Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/kmp
```

```nginx
server {
    listen 80;
    server_name your-domain.com;

    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### 3. Enable Site

```bash
sudo ln -s /etc/nginx/sites-available/kmp /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 4. Setup SSL with Let's Encrypt

```bash
sudo apt-get install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

## 📊 Monitoring

### View Container Stats

```bash
docker stats
```

### View Container Resource Usage

```bash
docker-compose top
```

## 🔄 Update Application

```bash
# Pull latest changes
git pull origin main

# Rebuild containers
docker-compose build --no-cache

# Restart containers
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate --force

# Clear cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

## 🐛 Troubleshooting

### Container won't start

```bash
# Check logs
docker-compose logs -f

# Check specific service
docker-compose logs -f app
docker-compose logs -f mysql
```

### Permission Issues

```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 /var/www/html/storage
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
```

### Database Connection Error

```bash
# Check if MySQL is running
docker-compose ps

# Wait for MySQL to be ready
docker-compose exec mysql mysqladmin ping -h localhost

# Check database credentials in .env
```

### Clear All Docker Cache

```bash
# Stop and remove all containers
docker-compose down -v

# Remove all unused images
docker system prune -a

# Rebuild from scratch
docker-compose build --no-cache
docker-compose up -d
```

## 📝 Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_NAME` | Application name | KMP |
| `APP_ENV` | Environment (local/production) | production |
| `APP_DEBUG` | Debug mode | false |
| `APP_URL` | Application URL | http://localhost:8000 |
| `DB_CONNECTION` | Database driver | mysql |
| `DB_HOST` | Database host | mysql |
| `DB_PORT` | Database port | 3306 |
| `DB_DATABASE` | Database name | kmp_db |
| `DB_USERNAME` | Database user | kmp_user |
| `DB_PASSWORD` | Database password | kmp_password |
| `APP_PORT` | Application port | 8000 |
| `PHPMYADMIN_PORT` | PHPMyAdmin port | 8080 |

## 📞 Support

Jika mengalami kendala, silakan check:
1. Container logs: `docker-compose logs -f`
2. Laravel logs: `storage/logs/laravel.log`
3. Apache logs: `docker-compose logs app`

---

**Created with ❤️ for KMP Application**
