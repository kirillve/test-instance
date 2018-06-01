#!/usr/bin/env bash

/app/bin/composer install --ansi --no-interaction

cp -f /app/config/apache2/sites-enabled/test.conf /etc/apache2/sites-enabled/test.conf

/etc/init.d/apache2 start
