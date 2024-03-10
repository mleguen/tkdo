#!/bin/sh

./composer.phar install

for vardir in cache doctrine/cache doctrine/proxy log; do
  [ -d ./var/$vardir ] || mkdir -p ./var/$vardir
  rm -rf ./var/$vardir/*
done

php wait-for-mysql.php || exit 1

./composer.phar doctrine -- orm:schema-tool:drop --force --full-database
./composer.phar doctrine -- orm:generate-proxies
./composer.phar doctrine -- migrations:migrate --no-interaction

chmod -R 777 ./var ./vendor
