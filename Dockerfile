FROM hyperf/hyperf:8.3-alpine-v3.22-swoole-v6 AS base

RUN apk add --no-cache  \
    vips vips-dev \
    libheif \
    libjpeg-turbo-dev \
    php83-ffi \
    && rm -rf /var/cache/apk/*

COPY ./backend /var/www/html/
WORKDIR /var/www/html

COPY entrypoint.sh /
EXPOSE 9501

ENTRYPOINT []
CMD ["/entrypoint.sh"]

FROM base AS local
RUN composer install

FROM base AS production
RUN composer install --prefer-dist --no-progress --no-interaction --classmap-authoritative --no-dev
COPY docker/php.ini /etc/php83/conf.d/production.ini


FROM docker.angie.software/angie:latest AS angie

COPY ./docker/angie/default.conf /etc/angie/http.d/default.conf
COPY ./docker/angie/angie.conf /etc/angie/angie.conf

EXPOSE 80
EXPOSE 443

CMD ["angie", "-g", "daemon off;"]