# Stage 1 — берем Composer
FROM composer:latest AS composer
FROM php:8.2-fpm

# Устанавливаем zip, unzip и git для Alpine
RUN apt-get update && apt-get install -y zip unzip git curl && docker-php-ext-install zip && apt-get clean

# Stage 2 — основной PHP
FROM php:8.2-fpm

WORKDIR /var/www

# Копируем composer внутрь контейнера
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Копируем проект
COPY src/ /var/www

EXPOSE 9000