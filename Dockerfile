FROM madpeter/phpapachepreload:latest

MAINTAINER Madpeter

COPY . /srv/website
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /srv/website

RUN \
    chmod +x .docker/CronEntrypoint.sh \
    && chown -R www-data:www-data /srv/website \
    && apt-get update