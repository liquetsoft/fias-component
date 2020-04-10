FROM php:7.4-cli-alpine


# alpine components
RUN set -xe && apk update && apk add --no-cache \
    shadow \
    libzip \
    bash \
    curl \
    icu-dev \
    libmcrypt-dev \
    libxml2-dev \
    postgresql-dev \
    libzip-dev \
    pcre-dev \
    git \
    autoconf \
    g++ \
    make \
    unrar


# php pecl components
RUN docker-php-ext-install pdo_pgsql pdo_mysql zip pcntl sockets soap intl opcache \
    && docker-php-source extract \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-source delete \
    && echo 'date.timezone=Europe/London' >> /usr/local/etc/php/conf.d/php-date.ini


# composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && mkdir -p /.composer \
    && chmod -Rf 777 /.composer


WORKDIR /var/app
