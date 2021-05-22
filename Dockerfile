FROM php:7.4-alpine

MAINTAINER Madpeter

COPY . /srv/website
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /srv/app

RUN docker-php-ext-install calendar mysqli && chown -R www-data:www-data /srv/website