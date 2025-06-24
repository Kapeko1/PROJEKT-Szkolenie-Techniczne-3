FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    postgresql-client

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip intl opcache

RUN pecl install redis && docker-php-ext-enable redis

RUN a2enmod rewrite headers

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

RUN useradd -m -u 1000 -s /bin/bash laravel

RUN chown -R laravel:laravel /var/www/html

USER laravel
