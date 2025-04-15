FROM php:7.4-fpm

RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    && curl -Lo /usr/local/bin/mhsendmail https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64 \
    && chmod a+x /usr/local/bin/mhsendmail \
    && echo 'sendmail_path = /usr/local/bin/mhsendmail ${MHSENDMAIL_OPTIONS}' > $PHP_INI_DIR/conf.d/php-sendmail.ini
