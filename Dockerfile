# Выбираем базовый образ
FROM php:8.2-apache

# Установка модулей Apache и PHP расширений
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
ENV DATABASE_URL="postgresql://postgres:aidalox2011@localhost:5432/task6"

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Конфигурация Apache, указание DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/symfony/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Устанавливаем рабочую директорию
WORKDIR /var/www/symfony

# Копируем файлы приложения
COPY . /var/www/symfony

# Установка зависимостей Composer
RUN composer install --no-dev --optimize-autoloader

# Открываем порт 80
EXPOSE 80

# Запуск Apache в фоновом режиме
CMD ["apache2-foreground"]
