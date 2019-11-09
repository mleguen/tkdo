.PHONY: build clean diff force install uninstall

build:

CONFIG = httpd.conf
CONFIG_ORIG = $(addsuffix .orig,$(CONFIG))
CONFIG_PATCH = $(addsuffix .patch,$(CONFIG))
clean: uninstall
	for f in $(CONFIG_ORIG) $(CONFIG_PATCH); do [ ! -f $$f ] || rm $$f; done

diff: $(CONFIG_PATCH)

IMAGE=tkdo/gateway
install: .docker-build

uninstall:
	[ ! -f .docker-build ] || (rm .docker-build && docker rmi $(IMAGE))

$(CONFIG_PATCH) : %.patch: %.orig
	diff $*.orig $* > $@ || true

$(CONFIG_ORIG): %.orig: force
	docker run --rm --entrypoint cat httpd:alpine /usr/local/apache2/conf/$* > $@

.docker-build: Dockerfile .dockerignore $(CONFIG)
	docker build -t $(IMAGE) .
	touch .docker-build
