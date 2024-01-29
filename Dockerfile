FROM php:8.1-cli

RUN apt-get update && \
    apt-get install -y \
            libzip-dev
RUN docker-php-ext-install zip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

WORKDIR /app
COPY . .

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install

ENTRYPOINT ["./shellvar"]
