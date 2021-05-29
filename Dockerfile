FROM php:7.4-apache

MAINTAINER Madpeter

COPY . /srv/website
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /srv/app

RUN apt-get update -y && apt-get install -y curl && apt-get clean -y && docker-php-ext-install calendar curl mysqli && chown -R www-data:www-data /srv/website && a2enmod rewrite