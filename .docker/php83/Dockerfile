FROM php:8.3-alpine

RUN apk add --update git \
    linux-headers \
    $PHPIZE_DEPS \
    openssh \
    zip

RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN docker-php-ext-install ftp pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY ./ /app/

RUN rm -f composer.lock && composer update

STOPSIGNAL SIGTERM

ENTRYPOINT ["tail", "-f", "/dev/null"]
