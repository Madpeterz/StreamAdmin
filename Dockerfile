FROM php:7.4-apache

MAINTAINER Madpeter

COPY . /srv/website
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /srv/app

RUN docker-php-ext-install calendar mysqli
RUN chown -R www-data:www-data /srv/website 
RUN a2enmod rewrite
