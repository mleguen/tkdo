.PHONY: clean diff force

CONFIG = httpd.conf
CONFIG_ORIG = $(addsuffix .orig,$(CONFIG))
CONFIG_PATCH = $(addsuffix .patch,$(CONFIG))

diff: $(CONFIG_PATCH)

$(CONFIG_PATCH) : %.patch: %.orig
	diff $*.orig $* > $@ || true

$(CONFIG_ORIG): %.orig: force
	docker run --rm --entrypoint cat httpd:alpine /usr/local/apache2/conf/$* > $@

clean:
	rm $(CONFIG_ORIG) $(CONFIG_PATCH)
