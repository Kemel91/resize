FROM hyperf/hyperf:8.3-alpine-v3.22-swoole-v6

WORKDIR /var/www/html

RUN apk add --no-cache  \
    vips vips-dev \
    libheif \
    php83-ffi \
    && rm -rf /var/cache/apk/*


COPY ./backend/composer.json ./backend/composer.lock /var/www/html/

# Копируем остальной код приложения
COPY ./backend /var/www/html/

# Устанавливаем зависимости PHP
RUN composer install --no-dev -o

EXPOSE 9501

ENTRYPOINT ["php", "/var/www/html/bin/hyperf.php", "start"]