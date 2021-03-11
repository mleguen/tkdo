FROM httpd:2.4
RUN sed -ri -e '/LoadModule (proxy_module|proxy_http_module)/s/^#//g' /usr/local/apache2/conf/httpd.conf &&\
        echo '\n\
<VirtualHost *:80>\n\
        ServerName front\n\
        ProxyPass "/api/" "http://slim/"\n\
        ProxyPassReverse "/api/" "http://slim/"\n\
        ProxyPass "/" "http://angular/"\n\
        ProxyPassReverse "/" "http://angular/"\n\
</VirtualHost>\n\
' >> /usr/local/apache2/conf/httpd.conf
