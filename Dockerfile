FROM php:8.3-apache

ENV LANG=C.UTF-8 \
    LC_ALL=C.UTF-8 \
    APACHE_DOCUMENT_ROOT=/var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        default-mysql-client \
        fonts-dejavu-core \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        curl \
        gd \
        intl \
        mbstring \
        mysqli \
        opcache \
        pdo_mysql \
        xml \
        zip \
    && a2enmod headers rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY docker/apache/agritwin.conf /etc/apache2/sites-available/000-default.conf
COPY docker/agritwin-entrypoint.sh /usr/local/bin/agritwin-entrypoint

WORKDIR /var/www/html/agritwin3
COPY . /var/www/html/agritwin3/

RUN chmod +x /usr/local/bin/agritwin-entrypoint \
    && mkdir -p \
        var/cache/openagri \
        var/queue/openagri \
        var/kpi \
        var/edge/cache \
        var/edge/queue \
        var/cloud_dss \
        var/reports \
        var/runtime \
    && chown -R www-data:www-data var

ENTRYPOINT ["agritwin-entrypoint"]
CMD ["apache2-foreground"]

EXPOSE 80
