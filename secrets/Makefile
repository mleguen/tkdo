.PHONY: build clean

SECRETS = cert.crt cert.pem auth-sp.key auth-sp.key.pub
build: $(SECRETS)

clean:
	for s in $(SECRETS); do [ ! -f s ] || rm s; done

cert.crt cert.pem: cert.config
	openssl req -config cert.config -new -x509 -nodes -out cert.crt -keyout cert.pem
	chmod 644 cert.crt cert.pem

%.key:
	openssl genrsa -out $*.key 2048
	chmod 644 $*.key

%.key.pub: %.key
	openssl rsa -in $*.key -outform PEM -pubout -out $*.key.pub
	chmod 644 $*.key.pub
