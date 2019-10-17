BUILD_DIR = domaine schema auth-sp back front

build:
	for d in $(BUILD_DIR); do $(MAKE) -C $$d $@; done

clean:
	for d in $(BUILD_DIR); do $(MAKE) -C $$d $@; done

install: build secrets
	docker-compose build

all: build doc

# Documentation

DOC_DIR = feature

doc: $(DOC_DIR)

# Sous-répertoires

$(BUILD_DIR) $(DOC_DIR):
	$(MAKE) -C $@

# Start/stop

start: install
	docker-compose up

stop:
	docker-compose down

# Secrets (si pas fournis)

CERT_MODS = auth-idp auth-sp gateway
SIGN_MODS = auth-sp

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

# Cibles qui ne doivent jamais être considérées comme à jour
.PHONY: build clean install secrets start stop doc 
