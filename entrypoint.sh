#!/bin/bash

set -x

if [ ! -f ./vendor/autoload.php ]
then
  echo "run composer install for dev environment"
  composer install --no-dev --no-progress --no-interaction --classmap-authoritative
fi

if [ ! -f .env ]
then
  echo "setup .env file"
  cp .env.example .env
  php artisan key:generate
fi

composer du
php /var/www/html/bin/hyperf.php start
