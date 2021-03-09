FROM httpd:2.4
RUN sed -ri -e '/LoadModule rewrite_module/s/^#//g' /usr/local/apache2/conf/httpd.conf &&\
        echo '\n\
<VirtualHost *:80>\n\
        RewriteEngine On\n\
        \n\
        RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -f [OR]\n\
        RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -d\n\
        RewriteRule ^.*$ - [NC,L]\n\
        RewriteRule ^(.*) /index.html [NC,L]\n\
        #LogLevel rewrite:trace6\n\
</VirtualHost>\n\
' >> /usr/local/apache2/conf/httpd.conf
