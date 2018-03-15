FROM 4xxi/php-mongo:php-7.2.2-fpm-alpine3.7 as deps
RUN addgroup -g 1000 -S nginx \
 && adduser -u 1000 -D -S -G nginx nginx

# Preparing

RUN mkdir -p /var/www/app
RUN chown -R nginx /var/www/app
WORKDIR /var/www/app
# Add our application files here
COPY . /var/www/app

# Install deps
RUN composer install --no-scripts --prefer-dist --no-dev --no-progress --no-suggest --optimize-autoloader --classmap-authoritative --no-interaction
