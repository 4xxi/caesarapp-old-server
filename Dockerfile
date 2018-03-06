# the application container
FROM 4xxi/php-mongo:7.2.2-fpm-alpine3.7
WORKDIR /var/www/app
COPY --from=4xxi/caesarapp-server:code /var/www/app /var/www/app

ENTRYPOINT  /var/www/app/entrypoint.sh
