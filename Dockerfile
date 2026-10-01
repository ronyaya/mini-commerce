FROM php:8.4-fpm

RUN apt-get update \
    && apt-get install -y \
        libpq-dev \
        unzip \
        git \
    && docker-php-ext-install pdo_pgsql \
    && pecl install redis \
    && docker-php-ext-enable redis

WORKDIR /var/www