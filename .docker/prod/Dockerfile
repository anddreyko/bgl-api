# syntax=docker/dockerfile:1

# ── Target: production ─────────────────────────────────────────────
# Lean image: nginx + php-fpm + app code (no dev deps, no xdebug)
FROM php:8.4-fpm-alpine AS production

RUN --mount=type=cache,target=/var/cache/apk \
    apk add --no-cache nginx postgresql-dev \
    && docker-php-ext-install opcache pdo_pgsql \
    && mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini

COPY .docker/base/php-fpm/conf.d /usr/local/etc/php/conf.d
COPY .docker/prod/php/conf.d     /usr/local/etc/php/conf.d

RUN rm -f /etc/nginx/http.d/default.conf
COPY .docker/base/nginx/conf.d/default.conf /etc/nginx/http.d/default.conf
COPY .docker/prod/nginx/conf.d/upstream.conf /etc/nginx/http.d/upstream.conf

ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

COPY src/    src/
COPY config/ config/
COPY web/    web/
COPY cli/    cli/

COPY .docker/prod/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 10000

ENTRYPOINT ["/entrypoint.sh"]

# ── Target: ci ─────────────────────────────────────────────────────
# Extends production + dev deps for scanning (psalm, deptrac, etc.)
FROM production AS ci

RUN composer install --optimize-autoloader --no-scripts --no-interaction
