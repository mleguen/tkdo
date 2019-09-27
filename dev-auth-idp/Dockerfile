FROM unicon/simplesamlphp

RUN	touch /var/simplesamlphp/modules/exampleauth/enable && \
  sed \
	-e "s/\('auth.adminpassword' => \).*,/\1'tkdo',/" \
	-e "s@\('baseurlpath' => \).*,@\1'https://localhost/auth-idp/',@" \
	-e "s/\('enable.saml20-idp' => \).*,/\1true,/" \
	-e "s/\('secretsalt' => \).*,/\1'$(tr -c -d '0123456789abcdefghijklmnopqrstuvwxyz' </dev/urandom | dd bs=32 count=1 2>/dev/null)',/" \
  -i /var/simplesamlphp/config/config.php

COPY authsources.php /var/simplesamlphp/config/
COPY saml20-sp-remote.php /var/simplesamlphp/metadata/
