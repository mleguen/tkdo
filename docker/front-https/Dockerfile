FROM httpd:2.4

# Enable required modules for HTTPS and proxying
RUN sed -ri -e '/LoadModule (proxy_module|proxy_http_module|ssl_module|socache_shmcb_module)/s/^#//g' /usr/local/apache2/conf/httpd.conf && \
    echo 'Include conf/extra/httpd-ssl.conf' >> /usr/local/apache2/conf/httpd.conf

# Create SSL configuration (dev-only: uses self-signed certs, not for production)
RUN echo '\n\
Listen 443\n\
SSLCipherSuite HIGH:!aNULL:!MD5:!RC4:!3DES\n\
SSLProxyCipherSuite HIGH:!aNULL:!MD5:!RC4:!3DES\n\
SSLHonorCipherOrder on\n\
SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1\n\
SSLProxyProtocol all -SSLv3 -TLSv1 -TLSv1.1\n\
SSLPassPhraseDialog  builtin\n\
SSLSessionCache        "shmcb:/usr/local/apache2/logs/ssl_scache(512000)"\n\
SSLSessionCacheTimeout  300\n\
\n\
<VirtualHost *:443>\n\
    ServerName localhost\n\
    SSLEngine on\n\
    SSLCertificateFile "/usr/local/apache2/conf/certs/localhost.crt"\n\
    SSLCertificateKeyFile "/usr/local/apache2/conf/certs/localhost.key"\n\
    ProxyPass "/api/" "http://slim-web/"\n\
    ProxyPassReverse "/api/" "http://slim-web/"\n\
    ProxyPass "/" "http://angular/"\n\
    ProxyPassReverse "/" "http://angular/"\n\
</VirtualHost>\n\
' > /usr/local/apache2/conf/extra/httpd-ssl.conf
