FROM hyperf/hyperf:8.3-alpine-v3.22-swoole-v6

RUN apk add --no-cache  \
    vips vips-dev \
    libheif \
    php83-ffi \
    && rm -rf /var/cache/apk/*

COPY ./backend /var/www/html/

WORKDIR /var/www/html

RUN composer install --prefer-dist --no-progress --no-interaction --classmap-authoritative --no-dev

COPY ./config/php.ini /etc/php83/conf.d/production.ini
COPY entrypoint.sh /
EXPOSE 9501

ENTRYPOINT []
CMD ["/entrypoint.sh"]