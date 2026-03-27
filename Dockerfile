FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev

RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    gd \
    zip \
    opcache \
    bcmath \
    intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/symfony

COPY composer.json ./

RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY . .

RUN mkdir -p /var/www/symfony/var
RUN chown -R www-data:www-data /var/www/symfony
RUN chmod -R 755 /var/www/symfony/var

EXPOSE 9000

CMD ["php-fpm"]
