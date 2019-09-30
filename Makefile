PROD_SERVICES = auth-sp front
DEV_SERVICES = dev-auth-idp dev-gateway
ALL_SERVICES = $(PROD_SERVICES) $(DEV_SERVICES)

build: $(PROD_SERVICES) .build

dev-build: build $(DEV_SERVICES) .dev-build

# Ne considère jamais aucun des modules comme à jour...
.PHONY: $(ALL_SERVICES)

# ... pour y forcer un make
$(ALL_SERVICES):
	$(MAKE) -C $@

.build: $(addsuffix /.build,$(PROD_SERVICES)) docker-compose.yml $(wildcard .env) auth-sp-jwt.key auth-sp-jwt.key.pub auth-sp-saml.crt auth-sp-saml.key
	touch .build

auth-sp-jwt.key:
	openssl genrsa -out auth-sp-jwt.key 2048
	chmod 644 auth-sp-jwt.key

auth-sp-jwt.key.pub: auth-sp-jwt.key
	openssl rsa -in auth-sp-jwt.key -outform PEM -pubout -out auth-sp-jwt.key.pub
	chmod 644 auth-sp-jwt.key.pub

auth-sp-saml.crt auth-sp-saml.key:
	openssl req -newkey rsa:3072 -new -x509 -days 3652 -nodes -out auth-sp-saml.crt -keyout auth-sp-saml.key
	chmod 644 auth-sp-saml.{crt,key}

.dev-build: .build $(addsuffix /.build,$(DEV_SERVICES)) docker-compose.override.yml dev-gateway-ssl.crt dev-gateway-ssl.key dev-auth-idp-saml.crt dev-auth-idp-saml.key
	docker-compose build
	touch .dev-build

dev-auth-idp-saml.crt dev-auth-idp-saml.key:
	openssl req -newkey rsa:3072 -new -x509 -days 3652 -nodes -out dev-auth-idp-saml.crt -keyout dev-auth-idp-saml.key
	chmod 644 dev-auth-idp-saml.{crt,key}

dev-gateway-ssl.crt dev-gateway-ssl.key:
	openssl req -new -x509 -nodes -out dev-gateway-ssl.crt -keyout dev-gateway-ssl.key
	chmod 644 dev-gateway-ssl.{crt,key}

start: dev-build
	(sleep 5 && xdg-open https://localhost)&
	docker-compose up

stop:
	docker-compose down
