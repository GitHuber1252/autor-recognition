FROM php:8.2-fpm

WORKDIR /var/www

# Install runtime dependencies for the PHP app
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        unzip \
        libcurl4-openssl-dev \
        libpq-dev \
        libzip-dev \
    && docker-php-ext-install curl zip pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*

# Install Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install dependencies without generating autoload yet
# (src/ is copied later)
COPY src/composer.json src/composer.lock* ./
RUN composer install --no-interaction --no-progress --prefer-dist --no-autoloader

# Copy application sources
COPY src/ /var/www/

# Generate autoload after sources exist
RUN composer dump-autoload --optimize

EXPOSE 9000