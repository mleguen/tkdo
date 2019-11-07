.PHONY: build clean install start stop uninstall

BUILD_DIR = auth-sp back db-tools feature front secrets
build:
	for d in $(BUILD_DIR); do $(MAKE) -C $$d $@; done

CLEAN_DIR = auth-idp auth-sp back domaine front gateway schema secrets
clean:
	for d in $(CLEAN_DIR); do $(MAKE) -C $$d $@; done

INSTALL_DIR = auth-idp auth-sp back db-tools front gateway
install: build secrets
	for d in $(INSTALL_DIR); do $(MAKE) -C $$d $@; done

start: install
	docker-compose up

stop:
	docker-compose down

uninstall:
	for d in $(INSTALL_DIR); do $(MAKE) -C $$d $@; done
