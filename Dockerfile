FROM php:8.2-cli

RUN docker-php-ext-install mysqli

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY . .

# Instalar dependencias (php-mqtt/client) definidas en composer.json
RUN composer install --no-dev --optimize-autoloader

EXPOSE 8080
CMD php -S 0.0.0.0:${PORT:-8080} -t /app