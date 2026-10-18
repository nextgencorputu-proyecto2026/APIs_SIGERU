FROM php:8.2-apache-bookworm

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Instalar dependencias y extensiones PHP
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libsqlite3-dev \
        libwebp-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        exif \
        gd \
        intl \
        mbstring \
        pcntl \
        pdo_mysql \
        pdo_sqlite \
        xml \
        zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Directorio de trabajo de Laravel
WORKDIR /var/www

# Copiar archivos de Composer
COPY composer.json composer.lock ./

# Instalar dependencias de producción
RUN composer install \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-dev \
    --optimize-autoloader \
    --no-scripts

# Copiar el proyecto Laravel de Usuarios
COPY . ./

# Generar autoload sin ejecutar scripts de desarrollo
RUN composer dump-autoload \
    --optimize \
    --no-dev \
    --no-interaction \
    --no-scripts \
    && chown -R www-data:www-data storage bootstrap/cache

# Configurar Apache para Laravel
COPY docker/apache/laravel.conf \
    /etc/apache2/sites-available/laravel.conf

RUN a2dissite 000-default \
    && a2ensite laravel

# Script de inicio exclusivo de API Usuarios
COPY docker/entrypoint.sh \
    /usr/local/bin/laravel-entrypoint

RUN sed -i 's/\r$//' /usr/local/bin/laravel-entrypoint \
    && chmod +x /usr/local/bin/laravel-entrypoint

ENTRYPOINT ["laravel-entrypoint"]

CMD ["apache2-foreground"]