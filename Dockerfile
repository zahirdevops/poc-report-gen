FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libzip-dev libpq-dev \
    && docker-php-ext-install zip pdo pdo_mysql pgsql pdo_pgsql \
    && pecl install redis \
    && docker-php-ext-enable redis


COPY webserver/php.ini /usr/local/etc/php/php.ini

COPY --from=composer:2.2.21 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html