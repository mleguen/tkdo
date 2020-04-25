#!/usr/bin/env bash
rm -r api
# See https://github.com/slimphp/Slim-Skeleton
# required php extensions apt packages: php-xml php-mbstring
composer create-project slim/slim-skeleton api
