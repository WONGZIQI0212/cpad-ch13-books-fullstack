FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader

FROM php:8.2-apache
RUN docker-php-ext-install pdo_mysql

COPY . /var/www/html
COPY --from=vendor /app/vendor /var/www/html/vendor

RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && a2enmod rewrite

EXPOSE 80