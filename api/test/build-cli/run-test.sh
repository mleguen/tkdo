#!/bin/sh
php wait-for-mysql.php || exit 1

for vardir in cache doctrine/cache doctrine/proxy log; do
  [ -d ./var/$vardir ] || mkdir -p ./var/$vardir
  rm -rf ./var/$vardir/*
done

./composer.phar doctrine -- orm:schema-tool:drop --force --full-database
./composer.phar doctrine -- orm:generate-proxies
./composer.phar doctrine -- migrations:migrate --no-interaction

chmod -R 777 ./var

./composer.phar test -- $@
