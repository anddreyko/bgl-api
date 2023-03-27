FROM richarvey/nginx-php-fpm:3.1.0

COPY .docker/base/nginx/conf.d /etc/nginx/conf.d
COPY .docker/base/php-fpm/conf.d /usr/local/etc/php/conf.d

# Image config
ENV WEBROOT /var/www/html/web
ENV PHP_ERRORS_STDERR 0
ENV ERRORS 0
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

ENV COMPOSER_ALLOW_SUPERUSER 1

WORKDIR /app

COPY ./composer.json ./composer.lock ./

RUN composer install --no-dev --prefer-dist --no-progress --no-suggest --no-scripts --optimize-autoloader --apcu-autoloader \
    && rm -rf /root/.composer/cache

COPY . .
