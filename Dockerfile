FROM php:7.4-apache

MAINTAINER Madpeter

COPY . /srv/website
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/crontab.default /etc/cron.d/crontab.default

WORKDIR /srv/app

# Install necessary packages / Install PHP extensions which depend on external libraries
RUN \
    apt-get update \
    && apt-get install -y openssl \
    && apt-get install -y cron \
    && chmod 0644 /etc/cron.d/crontab.default \
    && crontab /etc/cron.d/crontab.default \
    
    && echo 'Installing PHP curl extension' \
    && apt-get install -y --no-install-recommends libssl-dev libcurl4-openssl-dev \
    && docker-php-ext-configure curl --with-curl \
    && docker-php-ext-install -j$(nproc) \
        curl \
        mysqli \
        calendar \
        opcache \
    && chown -R www-data:www-data /srv/website \
    && a2enmod rewrite \ 
    && a2enmod expires \
    apt-get update

# Setup Zend OP Cache
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=1500'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.revalidate_freq=0'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini