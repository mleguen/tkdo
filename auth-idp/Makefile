.PHONY: clean diff force

CONFIG = $(addprefix config/,authsources.php config.php) metadata/saml20-sp-remote.php
CONFIG_ORIG = $(addsuffix .orig,$(CONFIG))
CONFIG_PATCH = $(addsuffix .patch,$(CONFIG))

diff: $(CONFIG_PATCH)

$(CONFIG_PATCH) : %.patch: %.orig
	diff $*.orig $* > $@ || true

$(CONFIG_ORIG): %.orig: force
	docker run --rm --entrypoint cat unicon/simplesamlphp /var/simplesamlphp/$* > $@

clean:
	rm $(CONFIG_ORIG) $(CONFIG_PATCH)
