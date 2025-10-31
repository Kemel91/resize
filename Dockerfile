FROM hyperf/hyperf:8.3-alpine-v3.22-swoole-v6

WORKDIR /var/www/html

RUN apk add --no-cache  \
    vips vips-dev \
    php83-ffi \
    && rm -rf /var/cache/apk/*

# Composer Cache
# COPY ./composer.* /opt/www/
# RUN composer install --no-dev --no-scripts

COPY ./backend /var/www/html
RUN composer install --no-dev -o && php bin/hyperf.php

EXPOSE 9501

ENTRYPOINT ["php", "/var/www/html/bin/hyperf.php", "start"]