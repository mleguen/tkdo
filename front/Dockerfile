FROM httpd:alpine

# Active HTTPS
RUN sed -i \
  -e 's/^#\(Include .*httpd-ssl.conf\)/\1/' \
  -e 's/^#\(LoadModule .*mod_ssl.so\)/\1/' \
  -e 's/^#\(LoadModule .*mod_socache_shmcb.so\)/\1/' \
  /usr/local/apache2/conf/httpd.conf

# Configure Apache (cf. https://angular.io/guide/deployment)
RUN sed -i \
  -e 's/^#\(LoadModule .*mod_rewrite.so\)/\1/' \
  -e '/<Directory "\/usr\/local\/apache2\/htdocs">/aRewriteEngine On\n\
  # If an existing asset or directory is requested go to it as it is\n\
  RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -f [OR]\n\
  RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} -d\n\
  RewriteRule ^ - [L]\n\
  # If the requested resource doesn\'t exist, use index.html\n\
  RewriteRule ^ /index.html' \
  /usr/local/apache2/conf/httpd.conf

# Copie l'application
COPY ./dist/front/ /usr/local/apache2/htdocs/
RUN chmod -R 644 /usr/local/apache2/htdocs/*
