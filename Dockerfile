# Dockerfile
FROM php:8.3-apache

# Instala a extensão PDO MySQL
RUN docker-php-ext-install pdo_mysql

# Copia o ficheiro‑fonte
COPY index.php /var/www/html/

# (Optional) expose port 80 explicitly (it is already the default for this image)
EXPOSE 80