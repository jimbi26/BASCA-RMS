FROM php:8.2-apache

# Install dependencies and PHP extensions (PDO_PGSQL, gd, curl)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    curl \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    git \
  && docker-php-ext-install pdo pdo_pgsql gd \
  && a2enmod rewrite headers

WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html

# Ensure uploads folder exists and is writable
RUN mkdir -p /var/www/html/uploads /var/www/html/uploads/ncsc_forms \
  && chown -R www-data:www-data /var/www/html \
  && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
