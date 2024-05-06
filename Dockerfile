# Usamos una imagen de PHP como base
FROM php:8.0-fpm

# Directorio de trabajo dentro del contenedor
WORKDIR /var/www

# Instalamos las dependencias necesarias
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql

# Copiamos los archivos de la aplicación al contenedor
COPY . /var/www

# Instalamos las dependencias de composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install

# Exponemos el puerto 9000 (que es el puerto por defecto de PHP-FPM)
EXPOSE 9000

# Comando para iniciar PHP-FPM
CMD ["php-fpm"]

