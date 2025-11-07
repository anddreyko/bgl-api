FROM php:8.4-cli-alpine

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && apk add --update linux-headers \
    && pecl install xdebug-stable \
    && docker-php-ext-enable xdebug \
    && apk del -f .build-deps \
    && rm -rf /tmp/pear

RUN docker-php-ext-install opcache

RUN apk add --no-cache unzip

RUN apk add --no-cache git \
    && git config --global --add safe.directory /app

COPY dev/php-cli/.gitconfig /root/.gitconfig
COPY dev/php-cli/.gitignore /root/.gitignore

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

COPY dev/php/conf.d /usr/local/etc/php/conf.d

COPY base/wait-for-it.sh /usr/local/bin/wait-for-it.sh
RUN chmod 555 /usr/local/bin/wait-for-it.sh

ENV COMPOSER_ALLOW_SUPERUSER 1

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet \
    && rm -rf /root/.composer/cache

WORKDIR /app
