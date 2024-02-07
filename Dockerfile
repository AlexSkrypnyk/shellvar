FROM php:8.3-cli as builder

RUN apt-get update \
    && apt-get install --no-install-recommends -y libzip-dev=1.7.3-1+b1 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install zip

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

COPY composer.json /app/

RUN COMPOSER_MEMORY_LIMIT=-1 composer install -n --ansi --prefer-dist --optimize-autoloader

COPY . /app

RUN composer build && cp /app/.build/shellvar /app/shellvar

FROM php:8.3-cli

RUN apt-get update \
    && apt-get install --no-install-recommends -y libzip-dev=1.7.3-1+b1 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/bin /usr/local/bin

RUN docker-php-ext-enable zip

WORKDIR /app

COPY --from=builder /app/shellvar /usr/local/bin/shellvar

ENTRYPOINT ["/usr/local/bin/shellvar"]
