FROM php:8.4-cli

RUN apt-get update && apt-get install --yes --no-install-recommends \
    git \
    libzip-dev \
    msmtp \
    unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    zip \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && echo 'sendmail_path = /usr/bin/msmtp --host maildev --port 1025 -t --read-envelope-from' > $PHP_INI_DIR/conf.d/php-sendmail.ini
