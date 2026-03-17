# Stage 1 — Composer (отдельный образ для бинарника)
FROM composer:latest AS composer

# Stage 2 — PHP-FPM
FROM php:8.2-fpm

# Копируем Composer из первого образа
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Устанавливаем системные зависимости (с авто-повторами при сбоях сети)
RUN for i in 1 2 3; do \
        apt-get update && \
        apt-get install -y --fix-missing --no-install-recommends \
            zip unzip git curl libzip-dev pkg-config && \
        break || \
        if [ $i -eq 3 ]; then \
            echo "All attempts failed, exiting."; \
            exit 1; \
        else \
            echo "Attempt $i failed, retrying in 5 seconds..."; \
            sleep 5; \
        fi \
    done && \
    docker-php-ext-install zip && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Устанавливаем рабочую директорию
WORKDIR /var/www

# Копируем исходный код и файлы Composer
COPY src/ /var/www/src
COPY composer.json composer.lock* ./

# Устанавливаем зависимости проекта (без dev‑пакетов — PHPUnit поставим глобально позже)
RUN composer install --no-interaction --no-dev

# Генерируем оптимизированный автозагрузчик (теперь папка src/ уже на месте)
RUN composer dump-autoload --optimize

# Добавляем путь к глобальным бинарникам Composer
ENV PATH="$PATH:/root/.composer/vendor/bin"

# Устанавливаем PHPUnit глобально
RUN composer global require phpunit/phpunit:^10

# Открываем порт (для FPM)
EXPOSE 9000