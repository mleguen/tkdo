#!/bin/sh
./install.sh

# Run tests with www-data to avoid file permissions on the shared doctrine cache
touch .phpunit.result.cache
chown www-data:www-data .phpunit.result.cache
sudo -Eu www-data ./composer.phar test -- $@
