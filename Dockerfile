FROM php:cli

# Install system dependencies and PHP extensions (pdo_mysql powers MariaDB)
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libonig-dev ca-certificates \
    && docker-php-ext-install pdo pdo_mysql mbstring zip bcmath \
    && rm -rf /var/lib/apt/lists/*

# Install latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-req=php

# Set folder permissions
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 10000

# Cache config, migrate MariaDB, run seeder only if RUN_SEED=true, and start server
CMD php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan migrate --force && \
    ([ "$RUN_SEED" = "true" ] && php artisan db:seed --force || true) && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
