FROM php:8.4-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    zip \
    unzip \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    oniguruma-dev

RUN docker-php-ext-install pdo_mysql mbstring zip gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-interaction --optimize-autoloader

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]