FROM php:7.3-apache
RUN docker-php-ext-install pdo_mysql
RUN a2enmod rewrite
RUN sed -ri -e 's!</VirtualHost>!\n\
        SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1\n\
        \n\
        RewriteEngine On\n\
        RewriteRule ^/?(.*) /public/index.php [NC,L]\n\
        #LogLevel rewrite:trace6\n\
&!' /etc/apache2/sites-available/000-default.conf
COPY php-error.ini $PHP_INI_DIR/conf.d/
RUN curl -Lo /usr/local/bin/mhsendmail https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64 &&\
        chmod a+x /usr/local/bin/mhsendmail &&\
        echo 'sendmail_path = /usr/local/bin/mhsendmail ${MHSENDMAIL_OPTIONS}' > $PHP_INI_DIR/conf.d/php-sendmail.ini
COPY entrypoint.sh .
RUN chmod u+x ./entrypoint.sh
ENTRYPOINT [ "./entrypoint.sh" ]
