#!/bin/sh
chmod -R 777 ./var

exec docker-php-entrypoint apache2-foreground
