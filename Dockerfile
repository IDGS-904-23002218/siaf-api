FROM php:8.2-cli

# unzip y libzip-dev son necesarios para que Composer pueda descomprimir
# los paquetes que descarga (vienen en formato .zip)
RUN apt-get update && apt-get install -y unzip libzip-dev \
    && docker-php-ext-install zip mysqli sockets \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY . .

# Instalar dependencias (php-mqtt/client) definidas en composer.json
RUN composer install --no-dev --optimize-autoloader

EXPOSE 8080
CMD php -S 0.0.0.0:${PORT:-8080} -t /app