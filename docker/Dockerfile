FROM php:8.3-fpm-alpine

# Instalar dependencias del sistema y herramientas de compilación
RUN apk add --no-cache \
    bash \
    git \
    icu-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    mysql-client

# Instalar extensiones PHP
RUN docker-php-ext-install \
    pdo_mysql \
    intl \
    gd \
    zip \
    bcmath \
    opcache

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuración de directorio de trabajo
WORKDIR /var/www/html

# Ajustar permisos para desarrollo
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

CMD ["php-fpm"]
