FROM php:8.1-cli

# hadolint ignore=DL3008
RUN apt-get update && \
    apt-get install --no-install-recommends -y \
            libzip-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install zip
# hadolint ignore=DL4006
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

WORKDIR /app
COPY . .

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install

ENTRYPOINT ["./shellvar"]
