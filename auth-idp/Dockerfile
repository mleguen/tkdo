FROM unicon/simplesamlphp

COPY config/ /var/simplesamlphp/config/
COPY metadata/ /var/simplesamlphp/metadata/

RUN	touch /var/simplesamlphp/modules/exampleauth/enable && \
  sed \
	-e "s/\('secretsalt' => \).*,/\1'$(tr -c -d '0123456789abcdefghijklmnopqrstuvwxyz' </dev/urandom | dd bs=32 count=1 2>/dev/null)',/" \
  -i /var/simplesamlphp/config/config.php && \
	chmod 644 /var/simplesamlphp/config/config.php && \
	chmod 644 /var/simplesamlphp/config/authsources.php && \
	chmod 644 /var/simplesamlphp/metadata/saml20-sp-remote.php
