## Multi-stage Dockerfile (node builder + php-apache production image)

## Single-stage: install Node and Composer into the Apache PHP image (faster to iterate for your workflow)

FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Noninteractive APT
ENV DEBIAN_FRONTEND=noninteractive

# Install system dependencies, GD dependencies and PHP extensions (use BuildKit cache)
RUN --mount=type=cache,target=/var/cache/apt/archives \
    --mount=type=cache,target=/var/lib/apt/lists \
    apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpq-dev \
    ca-certificates \
    gnupg \
    # Expose port 80 for Apache
    EXPOSE 80

# Use BuildKit cache mounts for apt to speed repeated builds (requires BuildKit)
ENV DEBIAN_FRONTEND=noninteractive
RUN --mount=type=cache,target=/var/cache/apt/archives \
    --mount=type=cache,target=/var/lib/apt/lists \
    apt-get update -yq \
    && apt-get install -y --no-install-recommends \
        git \
        curl \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        zip \
        unzip \
    && rm -rf /var/lib/apt/lists/* /var/cache/apt/archives/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Pin Composer to major version 2 for reproducibility
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Speed up builds by copying only dependency manifests first so layers cache when source changes
COPY composer.json composer.lock /var/www/html/
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www/html
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Copy application files and set ownership (this happens after deps are installed to keep cache)
COPY --chown=www-data:www-data . /var/www/html

# Copy built frontend assets from node-builder
# Adjust this path if your build outputs to a different directory
COPY --from=node-builder /app/public /var/www/html/public

# Configure Apache DocumentRoot to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/storage/logs \
        /var/www/html/storage/framework/sessions \
        /var/www/html/storage/framework/views \
        /var/www/html/storage/framework/cache \
        /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache



# Expose port 80
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
