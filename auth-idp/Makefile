.PHONY: build clean diff force install uninstall

build:

CONFIG = $(addprefix config/,authsources.php config.php) metadata/saml20-sp-remote.php
CONFIG_ORIG = $(addsuffix .orig,$(CONFIG))
CONFIG_PATCH = $(addsuffix .patch,$(CONFIG))
IMAGE=tkdo/auth-idp
clean: uninstall
	for f in $(CONFIG_ORIG) $(CONFIG_PATCH); do [ ! -f $$f ] || rm $$f; done

diff: $(CONFIG_PATCH)

install: .docker-build

uninstall:
	[ ! -f .docker-build ] || (rm .docker-build && docker rmi $(IMAGE))

$(CONFIG_PATCH) : %.patch: %.orig
	diff $*.orig $* > $@ || true

$(CONFIG_ORIG): %.orig: force
	docker run --rm --entrypoint cat unicon/simplesamlphp /var/simplesamlphp/$* > $@

.docker-build: Dockerfile .dockerignore $(CONFIG)
	docker build -t $(IMAGE) .
	touch .docker-build
