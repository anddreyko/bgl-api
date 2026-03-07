FROM php:8.4-cli-alpine

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && apk add --update linux-headers \
    && apk del -f .build-deps \
    && rm -rf /tmp/pear

RUN EXPECTED_SIGNATURE="$(wget -q -O - https://composer.github.io/installer.sig)" && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    ACTUAL_SIGNATURE="$(php -r "echo hash_file('sha384', 'composer-setup.php');")" && \
    if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ] ; then >&2 echo 'ERROR: Invalid installer signature'; rm composer-setup.php; exit 1; fi && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    rm composer-setup.php

RUN chmod +x /usr/local/bin/composer

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN mkdir -p -m 0777 /opt/composer
ENV COMPOSER_HOME=/opt/composer

RUN composer global require mcustiel/phiremock-server:^1.0 --prefer-dist --no-interaction --optimize-autoloader

RUN mkdir -p -m 0777 /opt/phiremock/expectation-files

EXPOSE 8086

CMD ["/opt/composer/vendor/bin/phiremock", "-d", "-p", "8086", "-i", "0.0.0.0", "-e", "/opt/phiremock/expectation-files"]
