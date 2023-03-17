FROM richarvey/nginx-php-fpm:3.1.0

COPY .docker/nginx/conf.d /etc/nginx/conf.d
COPY .docker/php-fpm/conf.d /usr/local/etc/php/conf.d
COPY . .

# Image config
ENV SKIP_COMPOSER 0
ENV WEBROOT /var/www/html/web
ENV PHP_ERRORS_STDERR 0
ENV ERRORS 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

ENV COMPOSER_ALLOW_SUPERUSER 1

RUN composer install --no-dev && rm -rf /root/.composer/cache

WORKDIR /app
