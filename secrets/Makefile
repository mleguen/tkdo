.PHONY: build clean

SECRETS = cert.crt cert.pem auth-sp.key auth-sp.key.pub mysql.pass mysql_root.pass
build: $(SECRETS)

clean:
	for s in $(SECRETS); do [ ! -f s ] || rm s; done

cert.crt cert.pem: cert.config
	openssl req -config cert.config -new -x509 -nodes -out cert.crt -keyout cert.pem
	chmod 644 cert.crt cert.pem

%.key:
	openssl genrsa -out $@ 2048
	chmod 644 $@

%.key.pub: %.key
	openssl rsa -in $*.key -outform PEM -pubout -out $@
	chmod 644 $@

%.pass:
	pwgen -1 16 > $@
	chmod 644 $@
