FROM php:8.2-fpm-alpine

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && apk add --update linux-headers \
    && pecl install xdebug-stable \
    && docker-php-ext-enable xdebug \
    && apk del -f .build-deps \
    && rm -rf /tmp/pear

RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo_pgsql

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

COPY base/php-fpm/conf.d /usr/local/etc/php/conf.d
COPY dev/php/conf.d /usr/local/etc/php/conf.d
COPY dev/php-fpm/conf.d /usr/local/etc/php/conf.d

WORKDIR /app
