# syntax = docker/dockerfile:1.2
FROM richarvey/nginx-php-fpm:3.1.6

#RUN --mount=type=secret,id=_env,dst=/etc/secrets/.env cat /etc/secrets/.env

USER ${USER}

COPY .docker/base/nginx/conf.d /etc/nginx/sites-available
COPY .docker/base/php-fpm/conf.d /usr/local/etc/php/conf.d

# Image config
ENV WEBROOT /app/web
ENV PHP_ERRORS_STDERR 0
ENV ERRORS 0
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
ENV SKIP_COMPOSER 0

ENV COMPOSER_ALLOW_SUPERUSER 1

WORKDIR /app

COPY ./composer.json ./composer.lock ./

RUN composer install --no-dev --prefer-dist --no-progress --no-suggest --no-scripts --optimize-autoloader --apcu-autoloader \
    && rm -rf /root/.composer/cache

COPY . .

RUN mkdir -p ./var && chown -R www-data:www-data ./var && chmod 777 ./var -R
