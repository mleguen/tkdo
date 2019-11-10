.PHONY: build clean install start stop uninstall

BUILD_DIR = auth-sp back db-tools feature front secrets
build:
	for d in $(BUILD_DIR); do $(MAKE) -C $$d $@; done

CLEAN_DIR = auth-idp auth-sp back front gateway schema secrets shared
clean: uninstall
	for d in $(CLEAN_DIR); do $(MAKE) -C $$d $@; done

INSTALL_DIR = auth-idp auth-sp back db-tools front gateway
install: build secrets
	for d in $(INSTALL_DIR); do $(MAKE) -C $$d $@; done
	docker-compose run service-db-tools typeorm migration:run
	docker-compose down

start: install
	docker-compose up

stop:
	docker-compose down

uninstall:
	for d in $(INSTALL_DIR); do $(MAKE) -C $$d $@; done
