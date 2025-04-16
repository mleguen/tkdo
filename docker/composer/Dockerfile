FROM php:8.4-cli
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

RUN apt update && apt install --yes \
    git \
    libzip-dev \
    unzip \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    zip \
    && curl -Lo /usr/local/bin/mhsendmail https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64 \
    && chmod a+x /usr/local/bin/mhsendmail \
    && echo 'sendmail_path = /usr/local/bin/mhsendmail ${MHSENDMAIL_OPTIONS}' > $PHP_INI_DIR/conf.d/php-sendmail.ini
