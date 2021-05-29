FROM php:7.4-apache

MAINTAINER Madpeter

COPY . /srv/website
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /srv/app


# Install necessary packages
RUN \
	apt-get update \
	&& apt-get install -y \
		openssl

# Install PHP extensions which depend on external libraries
RUN \
    apt-get update \
    && echo 'Installing PHP curl extension' \
    && apt-get install -y --no-install-recommends libssl-dev libcurl4-openssl-dev \
    && docker-php-ext-configure curl --with-curl \
    && docker-php-ext-install -j$(nproc) \
        curl \
        mysqli \
        calendar \
    && chown -R www-data:www-data /srv/website \
    && a2enmod rewrite
