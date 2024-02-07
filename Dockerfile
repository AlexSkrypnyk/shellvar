FROM php:8.3-cli

# Install php extensions.
# hadolint ignore=DL3008
RUN apt-get update && \
    apt-get install --no-install-recommends -y \
            libzip-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install zip

# Install composer.
# @see https://getcomposer.org/download
ENV COMPOSER_VERSION=2.5.8
ENV COMPOSER_SHA=e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02
ENV COMPOSER_ALLOW_SUPERUSER=1
# hadolint ignore=DL4006
RUN curl -L -o "/usr/local/bin/composer" "https://getcomposer.org/download/${COMPOSER_VERSION}/composer.phar" \
    && echo "${COMPOSER_SHA} /usr/local/bin/composer" | sha256sum \
    && chmod +x /usr/local/bin/composer \
    && composer --version \
    && composer clear-cache

WORKDIR /app
COPY . .

RUN composer install

CMD ["./shellvar"]
