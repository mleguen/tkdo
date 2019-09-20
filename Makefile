PROD_SERVICES = auth-sp front
DEV_SERVICES = dev-gateway
ALL_SERVICES = $(PROD_SERVICES) $(DEV_SERVICES)

build: $(PROD_SERVICES) .build

dev-build: build $(DEV_SERVICES) .dev-build

# Ne considère jamais aucun des modules comme à jour...
.PHONY: $(ALL_SERVICES)

# ... pour y forcer un make
$(ALL_SERVICES):
	$(MAKE) -C $@

.build: $(addsuffix /.build,$(PROD_SERVICES)) docker-compose.yml $(wildcard .env) auth-jwt.key auth-jwt.key.pub
	touch .build

auth-jwt.key:
	openssl genrsa -out auth-jwt.key 2048
	chmod 600 auth-jwt.key

auth-jwt.key.pub: auth-jwt.key
	openssl rsa -in auth-jwt.key -outform PEM -pubout -out auth-jwt.key.pub

.dev-build: .build $(addsuffix /.build,$(DEV_SERVICES)) docker-compose.override.yml dev-gateway-ssl.crt dev-gateway-ssl.key
	docker-compose build
	touch .dev-build

dev-gateway-ssl.crt dev-gateway-ssl.key:
	openssl req -new -x509 -nodes -out dev-gateway-ssl.crt -keyout dev-gateway-ssl.key
	chmod 600 dev-gateway-ssl.key

start: dev-build
	(sleep 5 && xdg-open https://localhost)&
	docker-compose up

stop:
	docker-compose down
