FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libapache2-mod-security2 \
    git \
    unzip \
    libzip-dev \
    libpq-dev \
    && a2enmod rewrite \
    && docker-php-ext-install \
    pdo_pgsql \
    zip

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV APACHE_DOCUMENT_ROOT /var/www/symfony/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/symfony

COPY . /var/www/symfony

RUN composer install --no-dev --optimize-autoloader

EXPOSE 80

CMD ["apache2-foreground"]
