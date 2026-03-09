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
    default-mysql-client

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

RUN a2enmod rewrite headers

WORKDIR /var/www/html

COPY . /var/www/html

RUN cd /var/www/html/api && composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

RUN sed -ri -e 's!/var/www/html!/var/www/html!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/var/www/html!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf


RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf


EXPOSE 80