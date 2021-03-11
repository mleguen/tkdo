#!/bin/sh
./install.sh
./composer.phar test -- $@
