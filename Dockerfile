FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql

WORKDIR /var/www/html
COPY . /var/www/html/
RUN mkdir -p var/cache/openagri var/queue/openagri var/kpi var/edge/cache var/edge/queue var/cloud_dss \
    && chown -R www-data:www-data var

EXPOSE 80
