FROM php:8.4-cli AS builder

RUN apt-get update \
    && apt-get install --no-install-recommends -y libzip-dev=1.7.3-1+b1 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install zip

# Install composer.
# @see https://getcomposer.org/download
ENV COMPOSER_VERSION=2.8.3
ENV COMPOSER_SHA=dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6
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

FROM php:8.4-cli

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
