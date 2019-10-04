BUILD_DIR = auth-sp back front
DOC_DIR = feature

CERT_MODS = auth-idp auth-sp gateway
SIGN_MODS = auth-sp

ALL_DIR = $(BUILD_DIR) $(DOC_DIR)

all: build doc

build: $(BUILD_DIR)
	docker-compose build

doc: $(DOC_DIR)

# Ne considère jamais aucun des répertoires comme à jour...
.PHONY: $(ALL_DIR)

# ... pour y forcer un make
$(ALL_DIR):
	$(MAKE) -C $@

start: build secrets
	(sleep 10 && xdg-open https://localhost)&
	docker-compose up

CERT_ROOTS = $(addsuffix -cert, $(CERT_MODS))
CERT_SECRETS = $(addsuffix .crt, $(CERT_ROOTS))  $(addsuffix .key, $(CERT_ROOTS))

SIGN_ROOTS = $(addsuffix -sign, $(SIGN_MODS))
SIGN_SECRETS = $(addsuffix .key, $(SIGN_ROOTS))  $(addsuffix .key.pub, $(SIGN_ROOTS))

secrets: $(CERT_SECRETS) $(SIGN_SECRETS)

%-sign.key:
	openssl genrsa -out $*-sign.key 2048
	chmod 644 $*-sign.key

%-sign.key.pub: %-sign.key
	openssl rsa -in $*-sign.key -outform PEM -pubout -out $*-sign.key.pub
	chmod 644 $*-sign.key.pub

%-cert.crt %-cert.key:
	openssl req -new -x509 -nodes -out $*-cert.crt -keyout $*-cert.key
	chmod 644 $*-cert.{crt,key}

stop:
	docker-compose down
