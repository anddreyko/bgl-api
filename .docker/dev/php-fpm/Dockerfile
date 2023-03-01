FROM php:8.4-fpm-alpine

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && apk add --update linux-headers \
    && pecl install xdebug-stable \
    && docker-php-ext-enable xdebug \
    && apk del -f .build-deps \
    && rm -rf /tmp/pear

RUN docker-php-ext-install opcache

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

COPY base/php-fpm/conf.d /usr/local/etc/php/conf.d
COPY dev/php/conf.d /usr/local/etc/php/conf.d
COPY dev/php-fpm/conf.d /usr/local/etc/php/conf.d

WORKDIR /app
