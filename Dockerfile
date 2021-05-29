FROM php:7.4-apache

MAINTAINER Madpeter

COPY . /srv/website
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /srv/app

RUN apt-get update && apt-get install -qq -y curl && apt-get install -qq -y php-curl && docker-php-ext-install calendar curl mysqli && chown -R www-data:www-data /srv/website && a2enmod rewrite