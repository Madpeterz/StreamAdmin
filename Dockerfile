# syntax=docker/dockerfile:1
#
# StreamAdmin 3.0 production image: nginx + php-fpm + the scheduler (s6) in one container.
#
#   docker build -t madpeter/streamadmin:3.0.0 .
#
# Settings come from the environment at run time (see .env.example). On start the base image runs the
# migrations and `php artisan optimize` (config, route, view and event caches), because the config
# cache has to be built with the run-time environment.
ARG PHP_IMAGE=serversideup/php:8.4-fpm-nginx

# --- PHP dependencies: no dev packages, class map authoritative autoloader ---------------------
FROM ${PHP_IMAGE} AS php-build
USER root
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative --no-scripts \
    && php artisan package:discover --ansi

# --- Front end: Tailwind scans the views and the Flux / table views in vendor/ ------------------
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
# class names that only live in PHP (table row styling in app/Livewire) must reach Tailwind too
COPY app ./app
COPY --from=php-build /var/www/html/vendor ./vendor
RUN npm run build
# pre-compressed copies so nginx serves them without compressing on every request
COPY docker/precompress.mjs ./
RUN node precompress.mjs public/build

# --- nginx Brotli module, built against the nginx in the base image -----------------------------
FROM ${PHP_IMAGE} AS brotli
USER root
RUN apt-get update \
    && apt-get install -y --no-install-recommends build-essential cmake git curl ca-certificates libpcre2-dev zlib1g-dev libssl-dev \
    && NGINX_VERSION="$(nginx -v 2>&1 | sed 's#.*/##')" \
    && curl -fsSL "https://nginx.org/download/nginx-${NGINX_VERSION}.tar.gz" | tar xz -C /tmp \
    && git clone --depth 1 --recurse-submodules --shallow-submodules https://github.com/google/ngx_brotli /tmp/ngx_brotli \
    && cd /tmp/ngx_brotli/deps/brotli && mkdir out && cd out \
    && cmake -DCMAKE_BUILD_TYPE=Release -DBUILD_SHARED_LIBS=OFF -DCMAKE_C_FLAGS="-O3 -fPIC" -DCMAKE_INSTALL_PREFIX=./installed .. \
    && cmake --build . --config Release -j"$(nproc)" --target brotlienc \
    && cd /tmp/nginx-${NGINX_VERSION} \
    && ./configure --with-compat --add-dynamic-module=/tmp/ngx_brotli \
    && make modules \
    && mkdir /modules && cp objs/ngx_http_brotli_*_module.so /modules/

# --- Runtime -------------------------------------------------------------------------------------
FROM ${PHP_IMAGE}
USER root

# Brotli for text responses (HTML, Livewire JSON) plus the pre-compressed build assets. The base image
# builds nginx.conf from this template on start, so the modules are loaded from the template.
COPY --from=brotli /modules/ /usr/lib/nginx/modules/
COPY docker/nginx/brotli.conf /etc/nginx/conf.d/brotli.conf
RUN sed -i '1i load_module modules/ngx_http_brotli_filter_module.so;\nload_module modules/ngx_http_brotli_static_module.so;' /etc/nginx/nginx.conf.template

# Scheduler service (starts only when SCHEDULER_ENABLED=true)
COPY docker/s6/ /etc/s6-overlay/
RUN chmod +x /etc/s6-overlay/s6-rc.d/laravel-scheduler/run

COPY --from=php-build --chown=www-data:www-data /var/www/html /var/www/html
COPY --from=assets --chown=www-data:www-data /app/public/build /var/www/html/public/build
RUN rm -rf docker tests Dockerfile docker-compose.dev.yml .env.dev.example \
    && mkdir -p storage/app/private/exports storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Shown at the bottom of every page. docker build --build-arg APP_VERSION=$(git describe --tags --always)
# --build-arg APP_BUILD_DATE="$(date -u +'%Y-%m-%d %H:%M UTC')"
ARG APP_VERSION=dev
ARG APP_BUILD_DATE=
ENV APP_VERSION=${APP_VERSION} \
    APP_BUILD_DATE=${APP_BUILD_DATE}

ENV AUTORUN_ENABLED=true \
    AUTORUN_LARAVEL_STORAGE_LINK=false \
    SSL_MODE=off \
    APP_ENV=production \
    APP_DEBUG=false \
    PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_OPCACHE_MEMORY_CONSUMPTION=256 \
    PHP_OPCACHE_MAX_ACCELERATED_FILES=20000

USER www-data
