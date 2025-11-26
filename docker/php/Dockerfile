
FROM php:8.2-apache

# Installation des dépendances
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug
ENV XDEBUG_MODE=coverage

# 👉 IMPORTANT : changer le DocumentRoot vers /public
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
