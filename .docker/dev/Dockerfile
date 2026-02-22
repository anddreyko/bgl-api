# syntax=docker/dockerfile:1

# ── Stage: xdebug-builder ───────────────────────────────────────────
# Compile xdebug once, reuse in php-fpm and php-cli targets
FROM php:8.4-cli-alpine AS xdebug-builder

RUN --mount=type=cache,target=/var/cache/apk \
    --mount=type=cache,target=/tmp/pear \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && apk add --update linux-headers \
    && pecl install xdebug-stable \
    && docker-php-ext-enable xdebug \
    && apk del -f .build-deps

# ── Target: nginx ───────────────────────────────────────────────────
FROM nginx:1.29-alpine AS nginx

COPY ./dev/nginx/conf.d /etc/nginx/conf.d

WORKDIR /app

# ── Target: php-fpm ─────────────────────────────────────────────────
FROM php:8.4-fpm-alpine AS php-fpm

COPY --from=xdebug-builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=xdebug-builder /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/

RUN --mount=type=cache,target=/var/cache/apk \
    apk add --no-cache postgresql-dev \
    && docker-php-ext-install opcache \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo_pgsql \
    && mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

COPY base/php-fpm/conf.d /usr/local/etc/php/conf.d
COPY dev/php/conf.d /usr/local/etc/php/conf.d
COPY dev/php-fpm/conf.d /usr/local/etc/php/conf.d

WORKDIR /app

# ── Target: php-cli ─────────────────────────────────────────────────
FROM php:8.4-cli-alpine AS php-cli

COPY --from=xdebug-builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=xdebug-builder /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/

RUN --mount=type=cache,target=/var/cache/apk \
    apk add --no-cache unzip postgresql-dev bash coreutils git linux-headers \
    && docker-php-ext-install opcache \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo_pgsql \
    && git config --global --add safe.directory /app \
    && mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

COPY dev/php-cli/.gitconfig /root/.gitconfig
COPY dev/php-cli/.gitignore /root/.gitignore
COPY dev/php/conf.d /usr/local/etc/php/conf.d
COPY base/wait-for-it.sh /usr/local/bin/wait-for-it.sh
RUN chmod 555 /usr/local/bin/wait-for-it.sh

ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
